<?php

namespace App\Http\Controllers;

use App\Enums\AuthenticateStateType;
use App\Models\AuthenticateState;
use App\Models\Authenticate;
use App\Models\User;
use App\Modifiers\AuthenticateServiceManager;
use App\Modifiers\AuthenticateStateModifier;
use App\Modifiers\UserModifier;
use Carbon\Carbon;
use Doctrine\ORM\EntityManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Laravel\Socialite\Contracts\User as LinkedAccount;
use Ramsey\Uuid\Uuid;
use Tymon\JWTAuth\JWTAuth;

class AuthController extends Controller
{
    /**
     * @var JWTAuth
     */
    protected $jwt;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var UserModifier
     */
    protected $userModifier;

    /**
     * @var AuthenticateServiceManager
     */
    protected $authenticateServiceManager;

    /**
     * @var AuthenticateStateModifier
     */
    protected $authenticateStateModifier;

    /**
     * AuthController constructor.
     * @param JWTAuth                    $jwt
     * @param EntityManager              $em
     * @param UserModifier               $userModifier
     * @param AuthenticateServiceManager $authenticateServiceManager
     * @param AuthenticateStateModifier  $authenticateStateModifier
     */
    public function __construct(
        JWTAuth $jwt,
        EntityManager $em,
        UserModifier $userModifier,
        AuthenticateServiceManager $authenticateServiceManager,
        AuthenticateStateModifier $authenticateStateModifier
    ) {
        $this->jwt = $jwt;
        $this->em = $em;
        $this->userModifier = $userModifier;
        $this->authenticateServiceManager = $authenticateServiceManager;
        $this->authenticateStateModifier = $authenticateStateModifier;
    }

    /**
     * @param Request $request
     * @param string  $serviceId
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function new(Request $request, string $serviceId)
    {
        $driver = $this
            ->authenticateServiceManager
            ->getDriver($request, $serviceId)
            ->stateless();

        $state = $this->authenticateStateModifier->create(
            $serviceId,
            AuthenticateStateType::NEW
        );

        return $driver
            ->with(['state' => $state->getStateId()])
            ->redirect();
    }

    /**
     * @param Request $request
     * @param string  $serviceId
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function add(Request $request, string $serviceId)
    {
        $driver = $this
            ->authenticateServiceManager
            ->getDriver($request, $serviceId)
            ->stateless();

        if (is_null(auth()->getUser())) {
            throw  new \RuntimeException('Not logged in.');
        }

        $state = $this->authenticateStateModifier->create(
            $serviceId,
            AuthenticateStateType::ADD,
            auth()->getUser()
        );

        return $driver
            ->with(['state' => $state->getStateId()])
            ->redirect();
    }

    /**
     * @param Request $request
     * @param string  $serviceId
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function login(Request $request, string $serviceId)
    {
        $driver = $this
            ->authenticateServiceManager
            ->getDriver($request, $serviceId)
            ->stateless();

        $state = $this->authenticateStateModifier->create(
            $serviceId,
            AuthenticateStateType::LOGIN
        );

        return $driver
            ->with(['state' => $state->getStateId()])
            ->redirect();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function callback(Request $request)
    {
        /** @var AuthenticateState $state */
        $state = $this
            ->em
            ->getRepository(AuthenticateState::class)
            ->find($request->input('state'));

        $serviceId = $state->getServiceId();

        if (is_null($state)) {
            throw new \RuntimeException('State not found.');
        }

        if ($state->hasExpired()) {
            throw new \RuntimeException('State expired.');
        }

        $this->em->remove($state);
        $this->em->flush();

        $linkedAccount = $this
            ->authenticateServiceManager
            ->getDriver($request, $serviceId)
            ->stateless()
            ->user();

        $authenticate = $this
            ->em
            ->getRepository(Authenticate::class)
            ->findOneBy([
                'serviceId' => $serviceId,
                'token'   => $linkedAccount->getId(),
            ]);

        switch ($state->getStateType()) {
            case AuthenticateStateType::NEW:
                if (!is_null($authenticate)) {
                    throw new \RuntimeException('This account already authenticated.');
                }
                $user = $this->userModifier->create($linkedAccount, $serviceId);
                break;
            case AuthenticateStateType::ADD:
                if (!is_null($authenticate)) {
                    throw new \RuntimeException('This account already authenticated.');
                }
                $user = $state->getUser();
                $this->authenticateModifier->add($linkedAccount);
                break;
            case AuthenticateStateType::LOGIN:
                if (is_null($authenticate)) {
                    throw new \RuntimeException('This account not authenticated.');
                }
                $user = $authenticate->getUser();
                break;
        }

        $token = $this->jwt->fromUser($user);

        return response()->json([
            'user' => $user,
            'token' => $token,
            'tokenExpiresIn' => auth()->factory()->getTTL() * 60,
        ]);
    }

    /**
     * @return JsonResponse
     */
    public function me()
    {
        return response()->json(auth()->user());
    }

    /**
     * @return JsonResponse
     */
    public function logout()
    {
        auth()->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * @return JsonResponse
     */
    public function refresh()
    {
        return response()->json([
            'token' => auth()->refresh(),
            'tokenExpiresIn' => auth()->factory()->getTTL() * 60,
        ]);
    }

    /**
     * @param LinkedAccount $linkedAccount
     * @param string        $serviceId
     * @return User
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function findOrCreateUser(LinkedAccount $linkedAccount, string $serviceId): User
    {
        $authenticate = $this
            ->em
            ->getRepository(Authenticate::class)
            ->findOneBy([
                'serviceName' => $serviceId,
                'serviceId'   => $linkedAccount->getId(),
            ]);

        if (is_null($authenticate)) {
            return $this->userModifier->create($linkedAccount, $serviceId);
        } else {
            return $authenticate->getUser();
        }
    }
}
