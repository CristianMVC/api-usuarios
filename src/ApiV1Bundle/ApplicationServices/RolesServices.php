<?php

namespace ApiV1Bundle\ApplicationServices;

use ApiV1Bundle\Entity\ValidateResultado;
use ApiV1Bundle\Entity\Validator\TokenValidator;
use ApiV1Bundle\Helper\JWToken;
use ApiV1Bundle\Repository\UsuarioRepository;

class RolesServices
{
    private $tokenValidator;
    private $usuarioRepository;

    /**
     * RolesServices constructor.
     * @param TokenValidator $tokenValidator
     * @param UsuarioRepository $usuarioRepository
     */
    public function __construct(
        TokenValidator $tokenValidator,
        UsuarioRepository $usuarioRepository
    ) {
        $this->tokenValidator = $tokenValidator;
        $this->usuarioRepository = $usuarioRepository;
    }

    /**
     * @param $authorization
     * @return ValidateResultado
     */
    public function getUsuario($authorization)
    {
        return $this->tokenValidator->validarToken($authorization);
    }
}
