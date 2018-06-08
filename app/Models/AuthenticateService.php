<?php

namespace App\Models;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use LaravelDoctrine\Extensions\SoftDeletes\SoftDeletes;
use LaravelDoctrine\Extensions\Timestamps\Timestamps;

/**
 * AuthenticateService
 *
 * @ORM\Table(name="authenticate_services")
 * @ORM\Entity
 */
class AuthenticateService
{
    use Timestamps;
    use SoftDeletes;

    /**
     * @var string
     *
     * @ORM\Id
     * @ORM\Column
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $serviceId;

    /**
     * @var string
     *
     * @ORM\Column(unique=true)
     */
    private $serviceName;

    /**
     * @var string
     *
     * @ORM\Column(type="authenticateServiceType")
     */
    private $serviceType;

    /**
     * @var string
     *
     * @ORM\Column
     */
    private $clientId;

    /**
     * @var string
     *
     * @ORM\Column
     */
    private $clientSecret;

    /**
     * @var string
     *
     * @ORM\Column
     */
    private $redirectUrl;

    /**
     * AuthenticateService constructor.
     * @param string $serviceId
     * @param string $serviceName
     * @param string $serviceType
     * @param string $clientId
     * @param string $clientSecret
     * @param string $redirectUrl
     */
    public function __construct(
        string $serviceId,
        string $serviceName,
        string $serviceType,
        string $clientId,
        string $clientSecret,
        string $redirectUrl
    ) {
        $this->serviceId = $serviceId;
        $this->serviceName = $serviceName;
        $this->serviceType = $serviceType;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->redirectUrl = $redirectUrl;
    }

    /**
     * @return string
     */
    public function getServiceType()
    {
        return $this->serviceType;
    }

    /**
     * @return string[]
     */
    public function getKeys(): array
    {
        return [
            $this->clientId,
            $this->clientSecret,
            $this->redirectUrl
        ];
    }
}
