<?php

namespace App\Http\Controllers;

use App\Models\AuthenticateState;
use App\Models\User\Authenticate;
use App\Models\User\User;
use App\Modifiers\AuthenticateServiceManager;
use App\Modifiers\AuthenticateStateModifier;
use App\Modifiers\UserModifier;
use Carbon\Carbon;
use Doctrine\ORM\EntityManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Socialite\Contracts\User as LinkedAccount;
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
     * @param string  $service
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function new(Request $request, string $service)
    {
        $driver = $this
            ->authenticateServiceManager
            ->getDriver($request, $service)
            ->stateless();

        $state = $this->authenticateStateModifier->create($service);

        return $driver
            ->with(['state' => $state->getStateId()])
            ->redirect();
    }

    /**
     * @param Request $request
     * @param string  $service
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function add(Request $request, string $service)
    {
        $driver = $this
            ->authenticateServiceManager
            ->getDriver($request, $service)
            ->stateless();

        if (is_null(auth()->getUser())) {
            throw  new \RuntimeException('Not logged in.');
        }

        $state = $this->authenticateStateModifier->create(
            $service,
            auth()->getUser()
        );

        return $driver
            ->with(['state' => $state->getStateId()])
            ->redirect();
    }

    /**
     * @param Request $request
     * @param string  $service
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function login(Request $request, string $service)
    {
        return $this
            ->authenticateServiceManager
            ->getDriver($request, $service)
            ->stateless()
            ->redirect();
    }

    // /**
    //  * @param Request $request
    //  * @param string  $service
    //  * @return \Symfony\Component\HttpFoundation\RedirectResponse
    //  */
    // public function redirect(Request $request, string $service)
    // {
    //     $driver = $this
    //         ->authenticateServiceManager
    //         ->getDriver($request, $service)
    //         ->stateless();
    //
    //     $state = $this->authenticateStateModifier->create(
    //         $service,
    //         auth()->getUser()
    //     );
    //
    //     return $driver
    //         ->with(['state' => $state->getStateId()])
    //         ->redirect();
    // }

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

        if (is_null($state)) {
            throw new \RuntimeException('State not found.');
        }

        if ($state->hasExpired()) {
            throw new \RuntimeException('State expired.');
        }

        $linkedAccount = $this
            ->authenticateServiceManager
            ->getDriver($request, $state->getServiceId())
            ->stateless()
            ->user();

        if ($state->isRegister()) {
            // 新規登録

        } else {
            // アカウントの追加
            $user = $state->getUser();
        }

        dump($linkedAccount);

        $user = $this->findOrCreateUser($linkedAccount, $service);
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
     * @param string        $service
     * @return User
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function findOrCreateUser(LinkedAccount $linkedAccount, string $service): User
    {
        $authenticate = $this
            ->em
            ->getRepository(Authenticate::class)
            ->findOneBy([
                'serviceName' => $service,
                'serviceId'   => $linkedAccount->getId(),
            ]);

        if (is_null($authenticate)) {
            return $this->userModifier->create($linkedAccount, $service);
        } else {
            return $authenticate->getUser();
        }
    }
}
