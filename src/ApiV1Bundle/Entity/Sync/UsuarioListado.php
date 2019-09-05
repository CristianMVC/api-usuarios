<?php

namespace ApiV1Bundle\Entity\Sync;


use ApiV1Bundle\ExternalServices\UsuarioIntegration;

class UsuarioListado
{
    /**
     * @var UsuarioIntegration
     */
    private $usuarioIntegration;

    /**
     *
     * @var array
     */
    private $mergedUsuarios ;
    /**
     * UsuarioListado constructor.
     * @param UsuarioIntegration $usuarioIntegration
     */
    
    public function __construct(
        UsuarioIntegration $usuarioIntegration
    )
    {
        $this->usuarioIntegration = $usuarioIntegration;
    }

    /**
     * Obtiene el listado de usuarios por rol de SNT y SNC
     *
     * @param $authorization
     * @param $offset
     * @param $limit
     * @param $params
     * @return array
     */
    public function getListadoUsuarios($authorization, $offset, $limit, $params)
    {
        
        return array_slice($this->getMergeUsuarios($authorization, $params), $offset, $limit);
    }

    /**
     * Retorna el total de usuarios
     *
     * @param $authorization
     * @param $params
     * @return int
     */
    public function getTotalListadoUsuarios($authorization, $params = [])
    {
        return count($this->getMergeUsuarios($authorization, $params));
    }

    /**
     * Realiza las llamadas a SNT y SNC y retorna un array mergeado de los usuarios
     *
     * @param $authorization
     * @param $param
     * @return array
     */
    private function getMergeUsuarios($authorization, $param = [])
    {
        if($this->mergedUsuarios){
            return $this->mergedUsuarios;
        }
        $param['nolimit'] = -1;
        $usuariosSNT = $this->usuarioIntegration->findAll('snt', ['Authorization' => $authorization], $param);
        $usuariosSNT['result'] = (is_null($usuariosSNT['result'])) ? [] :  $usuariosSNT['result'];
        $idsPuntoAtencion = $this->usuarioIntegration->getPuntosAtencion($authorization);
        $idsPuntoAtencion = (is_null($idsPuntoAtencion['result'])) ? [] : $idsPuntoAtencion['result'];
        $params = [
            "puntosatencion" =>   $idsPuntoAtencion
        ];
        $params = array_merge($params,$param);
        $validateResultado =  $this->usuarioIntegration->findAgentes($params, $authorization );
        if (! $validateResultado->hasError()) {
            $validate_result = $validateResultado->getEntity()['result'];
            return $this->setMergedUsuarios(array_merge( $usuariosSNT['result'], $validate_result?$validate_result:[]));
        }
        return $this->setMergedUsuarios(array_merge($usuariosSNT['result'], []));
    }
    
    public function setMergedUsuarios($mergedUsuarios) {
        return $this->mergedUsuarios = $mergedUsuarios;
    }
        

}