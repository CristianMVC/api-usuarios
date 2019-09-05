<?php

namespace ApiV1Bundle\ApplicationServices;

use ApiV1Bundle\Entity\ValidateResultado;
use ApiV1Bundle\Entity\Validator\ContrasenaValidator;
use ApiV1Bundle\Entity\Validator\TokenValidator;
use ApiV1Bundle\ExternalServices\ContrasenaIntegration;
use ApiV1Bundle\Helper\JWToken;
use ApiV1Bundle\Repository\UsuarioRepository;
use Symfony\Component\DependencyInjection\Container;

class ContrasenaServices extends SNTUserServices
{
    /** @var UsuarioRepository */
    private $usuarioRepository;

    /** @var ContrasenaValidator */
    private $contrasenaValidator;

    /** @var ContrasenaIntegration */
    private $contrasenaIntegration;

    /** @var TokenValidator */
    private $tokenValidator;

    /**
     * ContrasenaServices constructor.
     * @param Container $container
     * @param ContrasenaValidator $contrasenaValidator
     * @param UsuarioRepository $usuarioRepository
     * @param ContrasenaIntegration $contrasenaIntegration
     * @param TokenValidator $tokenValidator
     */
    public function __construct(
        Container $container,
        ContrasenaValidator $contrasenaValidator,
        UsuarioRepository $usuarioRepository,
        ContrasenaIntegration $contrasenaIntegration,
        TokenValidator $tokenValidator
    ) {
        parent::__construct($container);
        $this->contrasenaValidator = $contrasenaValidator;
        $this->usuarioRepository = $usuarioRepository;
        $this->contrasenaIntegration = $contrasenaIntegration;
        $this->tokenValidator = $tokenValidator;
    }


    /**
     * Recuperar contraseña  de usuario
     * @param array $params
     * @param $onSuccess
     * @param $onError
     * @return mixed
     */
    public function recuperarContrasena($params, $onSuccess, $onError)
    {
        $validateResultado = $this->contrasenaValidator->validarRecuperarContrasena($params);
        $resp = null;
        if (!$validateResultado->hasError()) {
            $usuario = $this->usuarioRepository->findOneByUsername($params['username']);
            if ($usuario) {
                $sistema = $usuario->getSistema();
                $username = $usuario->getUsername();
                $validateResultado = $this->contrasenaIntegration->recuperarContrasena($sistema, $username);
            } else {
                $validateResultado =
                    new ValidateResultado(null, ['Usuario no encontrado']);
            }
        }

        return $this->processResult(
            $validateResultado,
            function ($data) use ($onSuccess) {
                return $onSuccess($data['userMessage']);
            },
            $onError
        );
    }

    /**
     * Modificar contraseña de usuario
     *
     * @param $params
     * @param $authorization
     * @param $onSuccess
     * @param $onError
     * @return mixed
     */
    public function modificarContrasena($params, $authorization, $onSuccess, $onError)
    {
        $validateResultado = $this->contrasenaValidator->validarRecuperarContrasena($params);
        $resp = null;

        if (! $validateResultado->hasError()) {
            $usuario = $this->usuarioRepository->findOneByUsername($params['username']);
            if ($usuario) {
                $sistema = $usuario->getSistema();
                $validateResultado = $this->contrasenaIntegration->modificarContrasena(
                        $sistema,
                        $params,
                        $authorization
                    );
            } else {
                $validateResultado = new ValidateResultado(null, ['Usuario no encontrado']);
            }
        }

        return $this->processResult(
            $validateResultado,
            function ($data) use ($onSuccess) {
                return $onSuccess($data['userMessage']);
            },
            $onError
        );
    }

    /**
     * Validar token
     * 
     * @param $authorization
     * @param $onSuccess
     * @param $onError
     * @return mixed
     */
    public function isTokenValid($authorization, $onSuccess, $onError)
    {
        $validateResultado = $this->tokenValidator->validarToken($authorization, true);
        if (! $validateResultado->hasError()) {
            $token = $validateResultado->getEntity();
            $rol = JWToken::getRoles($token);
            $sistema = ($rol == 'ROL_AGENTE') ? 'snc' : 'snt';
            $validateResultado = $this->contrasenaIntegration->validarToken($sistema, $token);
        }

        return $this->processResult(
            $validateResultado,
            function ($data) use ($onSuccess) {
                return $onSuccess($data['userMessage'], $data['additional']);
            },
            $onError
        );
    }
}
