<?php
/**
 * Created by PhpStorm.
 * User: jtibi
 * Date: 15/2/2018
 * Time: 4:25 PM
 */

namespace ApiV1Bundle\ApplicationServices;


use ApiV1Bundle\Entity\Factory\UsuarioFactory;
use ApiV1Bundle\Entity\ValidateResultado;
use ApiV1Bundle\Entity\Validator\UsuarioValidator;
use ApiV1Bundle\ExternalServices\UsuarioIntegration;
use Symfony\Component\DependencyInjection\Container;

class VerificarUsuarioServices extends SNTUserServices
{
    /*
     * Security Integration
     */
    private $usuarioIntegration;

    /*
     * Usuario validator
     */
    private $usuarioValidator;

    /**
     * VerificarUsuarioServices constructor.
     * @param Container $container
     * @param UsuarioIntegration $usuarioIntegration
     * @param UsuarioValidator $usuarioValidator
     */
    public function __construct(
        Container $container,
        UsuarioIntegration $usuarioIntegration,
        UsuarioValidator $usuarioValidator
    ) {
        parent::__construct($container);
        $this->usuarioIntegration = $usuarioIntegration;
        $this->usuarioValidator = $usuarioValidator;
    }

    /**
     * Verifica si existe un usuario en SNC o SNT
     *
     * @param $usuario
     * @return array
     */
    public function verificarUsuario($usuario, $authorization)
    {
        // verificamos el usuario contra el sistema correspondiente
        if (!is_null($usuario->getSistema())) {
            $response = $this->usuarioIntegration->getByUserId($usuario, $authorization);
            if (! empty($response)) {
                return $response['result'];
            }
        }
        return [];
    }
}