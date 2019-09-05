<?php

namespace ApiV1Bundle\ExternalServices;

use ApiV1Bundle\Mocks\ExternalServiceMock;
use Symfony\Component\DependencyInjection\Container;

class ContrasenaIntegration extends Integration
{
    /** @var ExternalService */
    private $integrationService;

    /**
     * ContrasenaIntegration constructor.
     * @param Container $container
     * @param ExternalService $integrationService
     * @param ExternalServiceMock $integrationMock
     */
    public function __construct(
        Container $container,
        ExternalService $integrationService,
        ExternalServiceMock $integrationMock
    ) {
        parent::__construct($container);
        $this->integrationService = $integrationService;
        if ($this->getEnvironment() == 'test') {
            $this->integrationService = $integrationMock;
        }
    }

    /**
     * @param $params
     * @param $authorization
     * @return array
     */
    public function modificarContrasena($system, $params, $authorization)
    {
        $url = $this->integrationService->getUrl($system, 'modificar_contrasena');
        return $this->integrationService->post($url, $params, [
            'authorization' => $authorization
        ]);
    }

    /**
     * @param $sistema
     * @param $usuario
     * @return mixed
     */
    public function recuperarContrasena($sistema, $usuario)
    {
        $params = ['username' => $usuario];
        $url = $this->integrationService->getUrl($sistema, 'recuperar_contrasena');
        return $this->integrationService->post($url, $params);

    }

    /**
     * @param $sistema
     * @param $token
     * @return array
     */
    public function validarToken($sistema, $token)
    {
        $url = $this->integrationService->getUrl($sistema, 'validar_token');
        return $this->integrationService->post($url, [], ['Authorization' => "Bearer $token"]);
    }
}