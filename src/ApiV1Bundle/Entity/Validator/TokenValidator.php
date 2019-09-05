<?php

namespace ApiV1Bundle\Entity\Validator;

use ApiV1Bundle\Repository\UsuarioRepository;
use Symfony\Component\DependencyInjection\Container;
use ApiV1Bundle\Entity\ValidateResultado;
use ApiV1Bundle\Helper\JWToken;
use ApiV1Bundle\Repository\TokenRepository;

class TokenValidator extends SNTUserValidator
{
    private $usuarioRepository;
    private $tokenRepository;
    private $jwtoken;


    /**
     * TokenValidator constructor.
     * @param UsuarioRepository $userRepository
     * @param TokenRepository $tokenRepository
     * @param JWToken $jwtoken
     */
    public function __construct(
        UsuarioRepository $usuarioRepository,
        TokenRepository $tokenRepository,
        JWToken $jwtoken
    )
    {
        $this->usuarioRepository = $usuarioRepository;
        $this->tokenRepository = $tokenRepository;
        $this->jwtoken = $jwtoken;
    }

    /**
     * @param $authorization
     * @param bool $returnToken
     * @return ValidateResultado
     */
    public function validarToken($authorization, $returnToken = false)
    {
        $user = null;
        $errors = [];
        $dataToken = $this->validarAuthorization($authorization);

        if (! $dataToken->isValid()) {
            $errors[] = 'Token invalido';
            return new ValidateResultado(null, $errors);
        }

        if (! preg_match('/^Bearer\\s+(.)*?/', $authorization)) {
            return new ValidateResultado(null, ['token ausente']);
        }

        $token = preg_split('/\\s+/', $authorization);

        if (count($token) !== 2) {
            return new ValidateResultado(null, ['token invalido']);
        }

        $userID = $this->jwtoken->getUid($token[1]);
        if (isset($userID)) {
            $user = $this->usuarioRepository->findOneByUserId($userID);
        } else {
            $errors[] = 'El Token no es de un usuario valido.';
            return  new ValidateResultado(null, $errors);
        }

        if ($returnToken) {
            return new ValidateResultado($token[1],[]);
        }

        return new ValidateResultado($user, []);
    }


    /**
     * Validar authorization
     *
     * @param $authorization
     * @return mixed
     */
    public function validarAuthorization($authorization)
    {
        $token = md5($authorization);
        $tokenCancelado = $this->tokenRepository->findOneByToken($token);
        if ($authorization) {
            list($bearer, $token) = explode(' ', $authorization);
            $token = str_replace('"', '', $token);
        }
        return $this->jwtoken->validate($token, $tokenCancelado);
    }
}
