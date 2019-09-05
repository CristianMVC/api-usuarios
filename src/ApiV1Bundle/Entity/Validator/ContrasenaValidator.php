<?php

namespace ApiV1Bundle\Entity\Validator;

use ApiV1Bundle\Entity\ValidateResultado;
use Symfony\Component\DependencyInjection\Container;

class ContrasenaValidator extends SNTUserValidator
{
    /**
     * @var $container
     */
    private $container = null;

    /**
     * Constructor de la clase
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param $params
     * @return ValidateResultado
     */
    public function validarRecuperarContrasena($params)
    {
        $errors = $this->validar($params, [
            'username' => 'required'
        ]);
        return new ValidateResultado(null, $errors);
    }

    /**
     * @param $params
     * @return ValidateResultado
     */
    public function validarModificarContrasena($params)
    {
        $errors = $this->validar($params, [
            'username' => 'required',
            'nuevoPassword' => 'required'
        ]);

        return new ValidateResultado(null, $errors);
    }

    /**
     * @param $token
     * @return ValidateResultado
     */
    public function validarEstructuraToken($token)
    {
        $split = preg_split("/\s+/", $token);
        if (count($split) != 2) {
            return new ValidateResultado(null, ["Token inválido"]);
        }
        return new ValidateResultado($split[1], []);
    }
}
