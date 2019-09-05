<?php
/**
 * GrupoTramiteIntegration class
 *
 * @author Fausto Carrera <fcarrera@hexacta.com>
 */
namespace ApiV1Bundle\ExternalServices;

use Symfony\Component\DependencyInjection\Container;
use ApiV1Bundle\Mocks\ExternalServiceMock;

class SecurityIntegration extends Integration
{
    /**
     * Integration service
     */
    private $integrationService;

    /**
     * Class constructor
     *
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
     * Obtenemos el usuario del SNT
     *
     * @param $params
     * @return array
     */
    public function getUser($system, $params)
    {
        $url = $this->integrationService->getUrl($system, 'login');
        return $this->integrationService->post($url, $params);
    }

    /**
     * Logout user
     *
     * @param $system
     * @param $token
     * @return array
     */
    public function logout($system, $token)
    {
        $url = $this->integrationService->getUrl($system, 'logout');
        return $this->integrationService->post($url, null, ['authorization' => "Bearer {$token}"]);
    }

    /**
     * Obtenemos la jerarquía del punto de atención
     *
     * @param $pda
     * @return array
     */
    public function getJerarquiaPDA($pda)
    {
        $params = ['puntoatencion' => $pda];
        $url = $this->integrationService->getUrl('snt', 'jerarquia');
        return $this->integrationService->post($url, $params);
    }
}
