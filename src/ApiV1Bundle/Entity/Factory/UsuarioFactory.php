<?php
namespace ApiV1Bundle\Entity\Factory;

use ApiV1Bundle\Entity\ValidateResultado;
use ApiV1Bundle\Entity\Usuario;
use ApiV1Bundle\Entity\Validator\UsuarioValidator;
use ApiV1Bundle\ExternalServices\UsuarioIntegration;
use ApiV1Bundle\Repository\PuntoAtencionRepository;

/**
 * Class UsuarioFactory
 * @package ApiV1Bundle\Entity\Factory
 */

class UsuarioFactory
{
    /**
     * @var UsuarioValidator
     */
    private $usuarioValidator;

    /**
     * @var UsuarioIntegration
     */
    private $usuarioIntegration;

    /**
     * UsuarioFactory constructor.
     * @param UsuarioValidator $usuarioValidator
     * @param UsuarioIntegration $usuarioIntegration
     */
    public function __construct(
        UsuarioValidator $usuarioValidator,
        UsuarioIntegration $usuarioIntegration
    ) {
        $this->usuarioValidator = $usuarioValidator;
        $this->usuarioIntegration = $usuarioIntegration;
    }

    /**
     * Crea un usuario
     *
     * @param array $params array con los datos del usuario
     *
     * @return ValidateResultado
     */
    public function create($params, $sistema, $userId=null)
    {
        $validateResultados = $this->usuarioValidator->validarCreate($params, $sistema, $userId);
        if (! $validateResultados->hasError()) {
            $usuario = new Usuario(
                $params['nombre'],
                $params['apellido'],
                $params['username'],
                $params['rol'],
                $sistema,
                $userId
            );

            if (isset($params['puntoAtencion'])) {
                $usuario->setPuntoAtencionId($params['puntoAtencion']);
            }

            if (isset($params['organismo'])) {
                $usuario->setOrganismoId($params['organismo']);
            }

            if (isset($params['area'])) {
                $usuario->setAreaId($params['area']);
            }

            return new ValidateResultado($usuario, []);
        }
        return $validateResultados;
    }

    /**
     * Crea un usuario en el sistema correspondiente
     *
     * @param $params
     * @return mixed
     */
    public function save($params, $authorization)
    {
        if ($params['rol'] == Usuario::ROL_AGENTE) {
            return $this->usuarioIntegration->save($params, 'snc', $authorization);
        } else {
            return $this->usuarioIntegration->save($params, 'snt', $authorization);
        }
    }

    /**
     * obtiene el sistema dependiendo del rol del usuario
     *
     * @param $params
     * @return string
     */
    public function getSistema($params)
    {
        if ($params['rol'] == Usuario::ROL_AGENTE) {
            return 'snc';
        } else {
            return 'snt';
        }
    }
}
