<?php

namespace App\Modifiers;

use App\Models\Authenticate;
use App\Models\User;
use Doctrine\ORM\EntityManager;
use Laravel\Socialite\Contracts\User as LinkedAccount;

class UserModifier
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
     * @param LinkedAccount $linkedAccount
     * @param string        $serviceId
     * @return User
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function create(LinkedAccount $linkedAccount, string $serviceId): User
    {
        $user = new User($linkedAccount->getEmail());
        $this->em->persist($user);
        $authenticate = new Authenticate(
            $user,
            $serviceId,
            $linkedAccount->getId()
        );
        $user->addAuthenticate($authenticate);
        $this->em->persist($authenticate);

        $this->em->flush();

        return $user;
    }
}
