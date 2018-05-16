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
     * @param string $service
     * @param mixed  $user
     * @return AuthenticateState
     */
    public function create($service, $user = null): AuthenticateState
    {
        $userId = is_null($user) ? null : $user->getUserId();
        $authenticateState = new AuthenticateState($service, $userId);
        $this->em->persist($authenticateState);
        $this->em->flush();
        return $authenticateState;
    }
}
