<?php

namespace App\Modifiers;

use App\Models\AuthenticateState;
use Doctrine\ORM\EntityManager;

class AuthenticateStateModifier
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param      $serviceId
     * @param      $stateType
     * @param null $user
     * @return AuthenticateState
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function create($serviceId, $stateType, $user = null): AuthenticateState
    {
        $userId = is_null($user) ? null : $user->getUserId();
        $authenticateState = new AuthenticateState($serviceId, $stateType, $userId);
        $this->em->persist($authenticateState);
        $this->em->flush();
        return $authenticateState;
    }
}
