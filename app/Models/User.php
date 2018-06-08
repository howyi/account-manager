<?php

namespace App\Models;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Auth\Authenticatable;
use LaravelDoctrine\Extensions\Timestamps\Timestamps;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * User
 *
 * @ORM\Table(name="users")
 * @ORM\Entity
 */
class User implements AuthenticatableContract, JWTSubject, \JsonSerializable
{
    use Authenticatable;
    use Timestamps;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $userId;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true, unique=true)
     */
    private $name;

    /**
     * User constructor.
     *
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
        $this->authenticates = new ArrayCollection();
    }

    /**
     * @var \Doctrine\Common\Collections\Collection
     * @ORM\OneToMany(targetEntity="Authenticate", mappedBy="user")
     */
    private $authenticates;

    public function getUserId()
    {
        return $this->userId;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getKeyName()
    {
        return 'userId';
    }

    /**
     * Add authenticate.
     *
     * @param Authenticate $authenticate
     *
     * @return User
     */
    public function addAuthenticate(Authenticate $authenticate)
    {
        $this->authenticates[] = $authenticate;

        return $this;
    }

    /**
     * Remove authenticate.
     *
     * @param Authenticate $authenticate
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeAuthenticate(Authenticate $authenticate)
    {
        return $this->authenticates->removeElement($authenticate);
    }

    /**
     * Get authenticates.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAuthenticates()
    {
        return $this->authenticates;
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getUserId();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'user_id' => $this->getUserId(),
            'name' => $this->getName(),
        ];
    }
}
