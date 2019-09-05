<?php
namespace ApiV1Bundle\Entity\Sync;

use ApiV1Bundle\Entity\ValidateResultado;
use ApiV1Bundle\Entity\Validator\UsuarioValidator;
use ApiV1Bundle\Repository\UsuarioRepository;

/**
 * Class UsuarioSync
 * @package ApiV1Bundle\Entity\Sync
 */
class UsuarioSync
{
    private $usuarioRepository;
    private $usuarioValidator;

    /**
     * UsuarioSync constructor
     *
     * @param UsuarioRepository $usuarioRepository
     * @param UsuarioValidator $usuarioValidator
     */
    public function __construct(
        UsuarioRepository $usuarioRepository,
        UsuarioValidator $usuarioValidator
    ) {
        $this->usuarioRepository = $usuarioRepository;
        $this->usuarioValidator = $usuarioValidator;
    }

    /**
     * Editar usuario
     *
     * @param integer $id Identificador único del usuario
     * @param array $params array con datos del usuario
     *
     * @return ValidateResultado
     */

    public function edit($id, $params)
    {
        $validateResultado = $this->usuarioValidator->validarEdit($params);
        if (! $validateResultado->hasError()) {
            $usuario = $this->usuarioRepository->find($id);
            $usuario->setUsername($params['username']);
            $usuario->setNombre($params['nombre']);
            $usuario->setApellido($params['apellido']);

            if (isset($params['puntoatencion'])) {
                $usuario->setPuntoAtencionId($params['puntoatencion']);
            }

            if (isset($params['organismo'])) {
                $usuario->setOrganismoId($params['organismo']);
            }

            if (isset($params['area'])) {
                $usuario->setAreaId($params['area']);
            }

            $usuario->setUltimoLogin();
            $validateResultado->setEntity($usuario);
        }
        return $validateResultado;
    }

}
