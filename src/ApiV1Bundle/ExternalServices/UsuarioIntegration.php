<?php
namespace ApiV1Bundle\ExternalServices;

use ApiV1Bundle\Entity\Usuario;
use ApiV1Bundle\ExternalServices\ExternalService;
use ApiV1Bundle\Mocks\ExternalServiceMock;
use Symfony\Component\DependencyInjection\Container;


class UsuarioIntegration extends Integration
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
     * Obtiene el usuario del sistema correspondiente
     *
     * @param Usuario $usuario
     * @return array
     */
    public function getByUserId($usuario, $authorization)
    {
        $url = $this->integrationService->getUrl($usuario->getSistema(), 'usuarios', $usuario->getUserId());
        return $this->integrationService->get($url, null, ['Authorization' => $authorization]);
    }

    /**
     * Listar usuarios del SNT/SNC
     *
     * @param string $sistema 'snt'|'snc'
     * @param array $params 
     * @return array
     */
    public function findAll($sistema, $authorization, $params=[])
    {
        $url = $this->integrationService->getUrl($sistema, 'usuarios');
        return $this->integrationService->get($url, array_merge($params,['limit'=> PHP_INT_MAX ]), $authorization);
    }

    /**
     * Obtener los puntos de atención de SNT
     *
     * @return array
     */
    public function getPuntosAtencion($authorization)
    {
        $url = $this->integrationService->getUrl('snt', 'puntosatencion');
        return $this->integrationService->get($url, ['offset'=> 0, 'limit=' => null], ['Authorization' => $authorization]);
    }

    /**
     * Obtener los agentes de SNC
     *
     * @return array
     */
    public function findAgentes($params, $authorization)
    {
        $url = $this->integrationService->getUrl('snc', 'agentes');
        return $this->integrationService->post($url, $params, ['Authorization' => $authorization]);
    }


    /**
     * Editar usuario del SNT/SNC
     *
     * @param Usuario $usuario
     * @param array $params datos a editar
     * @return array
     */
    public function edit($usuario, $params, $authorization)
    {
        $url = $this->integrationService->getUrl($usuario->getSistema(), 'usuarios', $usuario->getUserId());
        return $this->integrationService->put($url, $params, ['Authorization' => $authorization]);
    }

    /**
     * Eliminamos usuario del SNT/SNC
     *
     * @param Usuario $usuario
     * @return array
     */
    public function delete($usuario, $authorization)
    {
        $url = $this->integrationService->getUrl($usuario->getSistema(), 'usuarios', $usuario->getUserId());
        return  $this->integrationService->delete($url, null, ['Authorization' => $authorization]);
    }

    /**
     * Guarda el usuario en el sistema que corresponda
     *
     * @param array $params datos a guardar
     * @return array
     */
    public function save($params, $sistema, $authorization)
    {
        $url = $this->integrationService->getUrl($sistema, 'usuarios');
        return $this->integrationService->post($url, $params, ['Authorization' => $authorization]);
    }
}
