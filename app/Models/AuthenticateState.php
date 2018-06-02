<?php

namespace App\Models;

use App\Enums\AuthenticateStateType;
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
     * @ORM\Column
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $stateId;

    /**
     * @var string
     *
     * @ORM\Column
     */
    private $serviceId;

    /**
     * @var string
     *
     * @ORM\Column(type="authenticateStateType")
     */
    private $stateType;

    /**
     * @var string
     *
     * @ORM\Column(nullable=true)
     */
    private $userId;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="user_id", nullable=true)
     */
    private $user;

    /**
     * AuthenticateState constructor.
     * @param string   $serviceId
     * @param string   $stateType
     * @param int|null $userId
     */
    public function __construct(
        string $serviceId,
        string $stateType,
        ?int $userId
    ) {
        $this->serviceId = $serviceId;
        $this->stateType = $stateType;
        $this->userId = $userId;
    }

    public function getStateId(): string
    {
        return $this->stateId;
    }

    public function getServiceId(): string
    {
        return $this->serviceId;
    }

    public function getStateType(): string
    {
        return $this->stateType;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function isNew(): bool
    {
        return AuthenticateStateType::NEW === $this->getStateType();
    }

    public function hasExpired(): bool
    {
        $now = Carbon::now()->getTimestamp();
        $created = $this->getCreatedAt()->getTimestamp();
        $ttl = \Config::get('app.state.ttl') * 60;
        return (($ttl + $created) <= $now);
    }
}
