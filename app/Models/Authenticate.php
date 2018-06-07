<?php

namespace App\Models;

use Doctrine\ORM\Mapping as ORM;
use LaravelDoctrine\Extensions\Timestamps\Timestamps;

/**
 * Authenticate
 *
 * @ORM\Table(name="authenticates")
 * @ORM\Entity
 */
class Authenticate
{
    use Timestamps;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $authenticateId;

    /**
     * @var string
     *
     * @ORM\Column
     */
    private $userId;

    /**
     * @var string
     *
     * @ORM\Column
     */
    private $serviceId;

    /**
     * @var string
     *
     * @ORM\Column
     */
    private $token;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="authenticates")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="user_id")
     */
    private $user;

    /**
     * @param User   $user
     * @param string $serviceId
     * @param string $token
     */
    public function __construct(
        User $user,
        string $serviceId,
        string $token
    ) {
        $this->user = $user;
        $this->userId = $user->getUserId();
        $this->serviceId = $serviceId;
        $this->token = $token;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }
}
