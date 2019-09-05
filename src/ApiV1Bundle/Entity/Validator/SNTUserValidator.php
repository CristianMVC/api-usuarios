<?php
namespace ApiV1Bundle\Entity\Validator;

use ApiV1Bundle\Helper\ServicesHelper;
use ApiV1Bundle\Entity\ValidateResultado;

class SNTUserValidator
{
    const CAMPO_REQUERIDO = '%s es un campo requerido';
    const CAMPO_NO_EXISTE = 'El campo %s no puede ser validado';
    const NUMERICO = '%s debe ser un valor numérico';
    const MATRIZ = '%s debe ser del tipo array.';
    const EMAIL = '%s debe ser una dirección de mail valida';
    const FECHA = '%s debe ser una fecha valida';
    const HORA = '%s debe ser una hora valida';
    const JSON = '%s debe ser un objeto JSON valido';
    const HORIZONTE = 'Horizonte fuera de rango';
    const INTERVALO = 'Intervalo fuera de rango';
    const STRING = 'Contiene caracteres no validos';

    /**
     * Validar campos según las reglas
     *
     * @param array $campos
     * @param array $reglas
     * @return array
     */
    public function validar($campos, $reglas)
    {
        $errores = [];
        foreach ($reglas as $key => $regla) {
            $validaciones = $this->getValidaciones($regla);
            // valido si el campo existe
            if (array_key_exists($key, $campos)) {
                $validacion = $this->validarReglas($validaciones, $campos, $key);
                if (count($validacion)) {
                    $errores[] = sprintf($validacion, ucfirst($key));
                }
            } else {
                // es no requerido, lo tenemos que validar
                if (in_array('required', $validaciones)) {
                    $errores[] = sprintf(self::CAMPO_NO_EXISTE, ucfirst($key));
                }
            }
        }
        return $errores;
    }


    /**
     * Obtener las reglas de validación de un campo
     *
     * @param string $regla
     * @return array
     */
    private function getValidaciones($regla)
    {
        return explode(':', $regla);
    }

    /**
     * Validar reglas
     *
     * @param array $validaciones
     * @param mixed $valor
     * @return array
     */
    private function validarReglas($validaciones, $campos, $key)
    {
        $errores = [];
        foreach ($validaciones as $validacion) {
            $error = $this->{trim($validacion)}($campos, $key);
            if ($error) {
                return $error;
            }
        }
        return $errores;
    }

    /**
     * Validar si es requerido
     *
     * @param mixed $valor
     * @return string|NULL
     */
    private function required($campos, $key)
    {
        // valido si el campo no fue seteado
        if (! isset($campos[$key])) {
            return self::CAMPO_REQUERIDO;
        }
        // si es un string, valido que no esté vacio
        if (is_string($campos[$key]) && empty($campos[$key]) && (string) $campos[$key] != '0') {
            return self::CAMPO_REQUERIDO;
        }
        return null;
    }

    /**
     * Validar si es un integro
     *
     * @param mixed $var
     * @return string|NULL
     */
    private function integer($campos, $key)
    {
        $isInt = (bool) filter_var($campos[$key], FILTER_VALIDATE_INT) || (string) $campos[$key] === '0';
        if (! $isInt) {
            return self::NUMERICO;
        }
        return null;
    }

    /**
     * Validar si es numérico
     *
     * @param mixed $var
     * @return string|NULL
     */
    private function numeric($campos, $key)
    {
        if (! is_numeric($campos[$key])) {
            return self::NUMERICO;
        }
        return null;
    }

    /**
     * Validar si es un email
     *
     * @param string $var
     * @return string|NULL
     */
    private function email($campos, $key)
    {
        if (! filter_var($campos[$key], FILTER_VALIDATE_EMAIL)) {
            return self::EMAIL;
        }
        return null;
    }

    /**
     * Validar si es un float
     *
     * @param mixed $var
     * @return string|NULL
     */
    private function float($campos, $key)
    {

        if (! filter_var($campos[$key], FILTER_VALIDATE_FLOAT)) {
            return self::NUMERICO;
        }
        return null;
    }

    /**
     * Validar si es una fecha
     *
     * @param mixed $date
     * @return string|NULL
     */
    private function date($campos, $key)
    {
        $format = 'Y-m-d';
        $date = $campos[$key];

        try {
            $d = new \DateTime(trim($date));
        } catch (\Exception $e) {
            return self::FECHA;
        }
        if (! ($d && $d->format($format) == trim($date))) {
            return self::FECHA;
        }
        return null;
    }

    /**
     * Validar si es fecha con zona horaria
     *
     * @param string $date
     * @return string|NULL
     */
    private function dateTZ($campos, $key)
    {
        $date = $campos[$key];
        $d = new \DateTime(trim($date));
        if (! ($d && $this->formatDateTZ($d) == trim($date))) {
            return self::FECHA;
        }
        return null;
    }

    /**
     * Validar si es una hora valida
     *
     * @param mixed $time
     * @return string|NULL
     */
    private function time($campos, $key)
    {
        $format = 'H:i';
        $time = $campos[$key];
        $d = new \DateTime(trim($time));
        if (! ($d && $d->format($format) == trim($time))) {
            return self::HORA;
        }
        return null;
    }

    /**
     * Formato de fecha con timezone
     *
     * @param \Datetime $date
     * @return string
     */
    private function formatDateTZ($date)
    {
        return $date->format('Y-m-d\TH:i:s') . '.' . substr($date->format('u'), 0, 3) . 'Z';
    }

    /**
     * Validar si el texto es JSON
     *
     * @param mixed $var
     * @return number|NULL
     */
    private function json($campos, $key)
    {
        // this is probably a JSON object already decoded
        if (is_array($campos[$key])) {
            return null;
        }
        if (is_string($campos[$key]) && is_null(json_decode($campos[$key]))) {
            return self::JSON;
        }
        return null;
    }

    /**
     * Validar si es un array
     *
     * @param array $var
     * @return string|NULL
     */
    private function matriz($campos, $key)
    {
        if (! ServicesHelper::isArray($campos[$key])) {
            return self::MATRIZ;
        }
        return null;
    }

    /**
     * Validar el horizonte
     *
     * @param $campos
     * @param $key
     * @return string|NULL
     */
    private function horizonte($campos, $key)
    {
        $horizonte = (int) $campos[$key];
        if ($horizonte < 1 || $horizonte > 365) {
            return self::HORIZONTE;
        }
        return null;
    }

    /**
     * Validar el intervalo
     *
     * @param $campos
     * @param $key
     * @return string|NULL
     */
    private function intervalo($campos, $key)
    {
        $intervalo = $campos[$key];
        if (! in_array($intervalo, ServicesHelper::intervalos())) {
            return self::INTERVALO;
        }
        return null;
    }

    /**
     * Validar si es un string
     *
     * @param $campos
     * @param $key
     * @return string|NULL
     */
    private function letters($campos, $key)
    {
        $word = $campos[$key];
        if (preg_match('/[0-9]/', $word)) {
            return self::STRING;
        }
        return null;
    }

    /**
     * Validamos la existencia de una entidad
     *
     * @param $entidad
     * @return ValidateResultado
     */
    public function validarEntidad($entidad, $mensaje)
    {
        $errors = [];
        if (! $entidad) {
            $errors[] = $mensaje;
        }
        return new ValidateResultado($entidad, $errors);
    }
}
