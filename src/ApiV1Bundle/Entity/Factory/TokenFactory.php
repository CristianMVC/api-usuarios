<?php
namespace ApiV1Bundle\Entity\Factory;

use ApiV1Bundle\Entity\ValidateResultado;
use ApiV1Bundle\Entity\Token;
use ApiV1Bundle\Repository\TokenRepository;

class TokenFactory
{

    private $tokenRepository;

    public function __construct(TokenRepository $tokenRepository)
    {
        $this->tokenRepository = $tokenRepository;
    }

    /**
     * Agrega un token al listado de tokens invalidados
     *
     * @param string $token
     * @return bool
     */
    public function insert($token)
    {
        $invalidToken = new Token($token);
        return new ValidateResultado($invalidToken, []);
    }
}
