<?php

namespace App\Models;

use Carbon\Carbon;
use Doctrine\ORM\Mapping as ORM;
use LaravelDoctrine\Extensions\SoftDeletes\SoftDeletes;
use LaravelDoctrine\Extensions\Timestamps\Timestamps;

/**
 * AuthenticateState
 *
 * @ORM\Table(name="authenticate_states")
 * @ORM\Entity
 */
class AuthenticateState
{
    use Timestamps;
    use SoftDeletes;

    /**
     * @var string
     *
     * @ORM\Id
     * @ORM\Column
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $stateId;

    /**
     * @var int
     *
     * @ORM\Column
     */
    private $serviceId;

    /**
     * @var string
     *
     * @ORM\Column(nullable=true)
     */
    private $userId;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="user_id")
     */
    private $user;

    /**
     * AuthenticateState constructor.
     * @param string   $serviceId
     * @param int|null $userId
     */
    public function __construct(
        string $serviceId,
        ?int $userId
    ) {
        $this->serviceId = $serviceId;
        $this->userId = $userId;
    }

    public function getStateId(): string
    {
        return $this->stateId;
    }

    public function getServiceId(): int
    {
        return $this->serviceId;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function isRegister(): bool
    {
        return is_null($this->userId);
    }

    public function hasExpired(): bool
    {
        $now = Carbon::now()->getTimestamp();
        $created = $this->getCreatedAt()->getTimestamp();
        $ttl = \Config::get('app.state.ttl') * 60;
        return (($ttl + $created) <= $now);
    }
}
