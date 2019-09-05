<?php
namespace ApiV1Bundle\Entity\Validator;

use Symfony\Component\DependencyInjection\Container;
use ApiV1Bundle\Entity\ValidateResultado;

class LoginValidator extends SNTUserValidator
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
     * Validar parámetros del login
     * @param array $params
     * @return \ApiV1Bundle\Entity\ValidateResultado
     */
    public function validateLogin($params)
    {
        $errors = $this->validar($params, [
            'username' => 'required:email',
            'password' => 'required'
        ]);
        return new ValidateResultado($params, $errors);
    }

    /**
     * Validamos el usuarios que nos viene desde SNT o SNC
     * @param $user
     * @return \ApiV1Bundle\Entity\ValidateResultado
     */
    public function validateUser($user)
    {
        if (is_null($user)) {
            return new ValidateResultado($user, ['Usuario/contraseña invalidos']);
        }
        // verificamos errores
        $errors = $this->validar($user, [
            'id' => 'required',
            'username' => 'required',
            'token' => 'required',
            'rol' => 'required'
        ]);
        if (count($errors)) {
            return new ValidateResultado($user, ['Usuario/contraseña invalidos']);
        }
        return new ValidateResultado($user, []);
    }
}
