<?php
namespace ApiV1Bundle\Entity\Validator;

use Symfony\Component\DependencyInjection\Container;
use ApiV1Bundle\Entity\ValidateResultado;

class UsuarioValidator extends SNTUserValidator
{
    /**
     * @var $container
     */
    private $container = null;

    /**
     * Constructor
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Validar la creación del usuario
     * @param $username
     * @param $sistema
     * @param $userId
     * @return \ApiV1Bundle\Entity\ValidateResultado
     */
    public function validarCreate($params, $sistema, $userId=null)
    {

        $errors = $this->validar([
            'nombre' => $params['nombre'],
            'apellido' => $params['apellido'],
            'username' => $params['username'],
            'rol' => $params['rol'],
            'sistema' => $sistema
        ], [
            'nombre' => 'required',
            'apellido' => 'required',
            'rol' => 'required:integer',
            'username' => 'required',
            'sistema' => 'required'
        ]);

        return new ValidateResultado(null, $errors);
    }

    /**
     * Validamos los parámetros de edición del usuario
     * @param $usuario
     * @param $username
     * @return \ApiV1Bundle\Entity\ValidateResultado
     */
    public function validarEdit($params)
    {
        $errors = $this->validar($params, [
            'nombre' => 'required',
            'apellido' => 'required',
            'username' => 'required:email',
            'rol' => 'required:integer'
        ]);
        return new ValidateResultado(null, $errors);
    }

    /**
     * Validamos el usuario
     * @param $usuario
     * @return \ApiV1Bundle\Entity\ValidateResultado
     */
    public function validarUsuario($usuario)
    {
        $errors = [];
        if (! $usuario) {
            $errors[] = 'Usuario inexistente';
        }

        return new ValidateResultado(null, $errors);
    }

    /**
     * Validamos el usuario no exista
     * @param $usuario
     * @return \ApiV1Bundle\Entity\ValidateResultado
     */
    public function validarUsuarioExistente($usuario)
    {
        $errors = [];
        if ($usuario) {
            $errors[] = 'Ya existe un usuario con el email ingresado';
        }

        return new ValidateResultado(null, $errors);
    }

    /**
     * Valida que los datos ingresados sean validos
     *
     * @param $params
     * @param $usuario
     * @return ValidateResultado
     */
    public function validarAddUsuario($params, $usuario)
    {
        $validateResultado = $this->validarUsuarioExistente($usuario);

        if (! $validateResultado->hasError()) {
            $errors = $this->validar($params, [
                'nombre' => 'required',
                'apellido' => 'required',
                'username' => 'required:email',
                'rol' => 'required:integer'
            ]);

            return new ValidateResultado(null, $errors);
        }
        return $validateResultado;
    }
}
