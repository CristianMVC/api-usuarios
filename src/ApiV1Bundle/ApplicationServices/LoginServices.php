<?php
namespace ApiV1Bundle\ApplicationServices;

use Symfony\Component\DependencyInjection\Container;
use ApiV1Bundle\Entity\Usuario;
use ApiV1Bundle\Repository\UsuarioRepository;
use ApiV1Bundle\Entity\Factory\UsuarioFactory;
use ApiV1Bundle\Entity\Validator\UsuarioValidator;
use ApiV1Bundle\Entity\Validator\LoginValidator;
use ApiV1Bundle\ExternalServices\SecurityIntegration;
use ApiV1Bundle\Helper\JWToken;
use ApiV1Bundle\ExternalServices\UsuarioIntegration;
use ApiV1Bundle\Repository\PuntoAtencionRepository;
use ApiV1Bundle\Entity\Response\RespuestaConEstado;

class LoginServices extends SNTUserServices
{

    /**
     * Integration service
     */
    private $integrationService;

    /**
     * User repository
     */
    private $usuarioRepository;

    /**
     * Login validator
     */
    private $loginValidator;

    /**
     * Usuario validator
     */
    private $usuarioValidator;

    /**
     * JWToken
     */
    private $jwtoken;

    /**
     * @var UsuarioIntegration
     */
    private $usuarioIntegration;

    /**
     * Security services
     *
     * @param Container $container
     * @param SecurityIntegration $integration
     */
    public function __construct(
        Container $container,
        SecurityIntegration $integrationService,
        UsuarioRepository $usuarioRepository,
        UsuarioValidator $usuarioValidator,
        UsuarioIntegration $usuarioIntegration,
        LoginValidator $loginValidator,
        JWToken $jwtoken
    ) {
        parent::__construct($container);
        $this->integrationService = $integrationService;
        $this->usuarioRepository = $usuarioRepository;
        $this->usuarioValidator = $usuarioValidator;
        $this->usuarioIntegration = $usuarioIntegration;
        $this->loginValidator = $loginValidator;
        $this->jwtoken = $jwtoken;
    }

    /**
     * Loguearse sólo con username y contraseña
     * @param $params
     */
    public function loginSNU($params){
        return $this->login(
            $params,
            function ($response) {
                return $response;
            },
            function ($err) {
                return $err;
            }
        );
    }
    /**
     * Login de usuarios
     * @param $params
     * @param $onError
     * @return string[]
     */
    public function login($params, $onSuccess, $onError)
    {
        $validateResult = $this->loginValidator->validateLogin($params);
        $result = null;
        if (! $validateResult->hasError()) {
            // verificamos si el usuario está ne la base local
            $usuario = $this->usuarioRepository->findOneByUsername($params['username']);
            if ($usuario) {
                $validateResult = $this->loginUser($usuario, $params);
                if (! $validateResult->hasError()) {
                    $result = $validateResult->getEntity();
                    $usuario->setUltimoLogin();
                }
            } else {
                // chequeamos el usuario contra el SNT y el SNC
                $result = $this->verifyUser($params);
            }

            // validamos el resultado
            $validateResult = $this->loginValidator->validateUser($result);
        }
        return $this->processResult(
            $validateResult,
            function () use ($onSuccess, $result) {
                // generamos el token que va a usar el usuario en todos los sitios
                $result['token'] = $this->jwtoken->getToken($result['id'], $result['username'], $result['rol'], $result['puntoAtencion']['id']);
                return call_user_func($onSuccess, $result, $this->usuarioRepository->flush());
            },
            $onError
        );
    }

    /**
     * Login del usuario
     *
     * @param $usuario
     * @param $params
     * @return array
     */
    public function loginUser($usuario, $params)
    {
        $sistema = $usuario->getSistema();
        // login en snt
        if ($sistema == 'snt') {
            return $this->integrationService->getUser('snt', $params);
        }
        // login en snc
        if ($sistema == 'snc') {
            return $this->integrationService->getUser('snc', $params);

        }
    }

    /**
     * Verificamos si el usuario pertenece a SNT o a SNC
     *
     * @param $params
     * @return mixed
     */
    private function verifyUser($params)
    {
        // verificamos el usuario contra el SNT (le damos prioridad)
        $validateResult = $this->integrationService->getUser('snt', $params);
        if (! $validateResult->hasError()) {
            $sntLoginResponse = $validateResult->getEntity();
            if ($sntLoginResponse && isset($sntLoginResponse['token'])) {
                $this->saveUser($params, 'snt', $sntLoginResponse);
                return $sntLoginResponse;
            }
        }

        // verificamos al usuario contra el SNC
        $validateResult = $this->integrationService->getUser('snc', $params);
        if (! $validateResult->hasError()) {
            $sncLoginResponse = $validateResult->getEntity();
            if ($sncLoginResponse && isset($sncLoginResponse['token'])) {
                if ($sncLoginResponse['rol'] != 'ROL_AGENTE') {
                    return null;
                }
                $this->saveUser($params, 'snc', $sncLoginResponse);
                return $sncLoginResponse;
            }
        }
        return null;
    }

    /**
     * Guardamos al usuario en la base de datos de usuarios
     * @param $params
     * @param $sistema
     * @param $response
     */
    private function saveUser($params, $sistema, $response)
    {
        // creamos una nueva entrada en la base de datos
        $usuarioFactory = new UsuarioFactory(
            $this->usuarioValidator,
            $this->usuarioIntegration
        );
        // seteamos el rol
        $response['rol'] = $response['rol_id'];
        $response['puntoAtencion'] = $response['puntoAtencion']['id'];
        $usuarioCreate = $usuarioFactory->create($response, $sistema, $response['id']);
        if (!$usuarioCreate->hasError()) {
            $usuario = $usuarioCreate->getEntity();
            $usuario->setUltimoLogin();
            $this->usuarioRepository->add($usuario);
        }
        return;
    }
}
