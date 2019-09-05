<?php

namespace ApiV1Bundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;

/**
 * Token
 *
 * @ORM\Table(name="token", indexes={@Index(name="search_idx", columns={"token"})})
 * @ORM\Entity(repositoryClass="ApiV1Bundle\Repository\TokenRepository")
 */
class Token
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="token", type="string", length=32, unique=true)
     */
    private $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set token
     *
     * @param string $token
     *
     * @return token
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get token
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }
}
