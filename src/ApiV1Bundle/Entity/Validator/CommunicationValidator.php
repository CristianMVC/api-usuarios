<?php
namespace ApiV1Bundle\Entity\Validator;

use ApiV1Bundle\Entity\ValidateResultado;
use Symfony\Component\DependencyInjection\Container;

class CommunicationValidator extends SNTUserValidator
{
    private $apiId = [];
    private $keys = [];

    public function __construct(Container $container)
    {
        $config = $container->getParameter('integration');
        $this->apiId = $config['api_id'];
        $this->keys = $config['keys'];
    }

    /**
     * Validamos los datos que vienen desde el Sistema Nacional de Turnos
     *
     * @param array $response
     * @return ValidateResultado
     */
    public function validateSNTRequest($request)
    {
        $errors = [];
        // verficar si tiene el api_id
        if (! isset($request['api_id'])) {
            $errors[] = 'API ID incorrecto';
        }
        // verificamos que exista la firma
        if (! isset($request['signature'])) {
            $errors[] = 'No se puede verificar la identidad del emisor';
        }
        // validamos que los ids de las apis coincidan
        if (isset($request['api_id']) && $request['api_id'] !== $this->apiId['snt']) {
            $errors[] = 'API ID incorrecto';
        }
        // si no hay errores, validamos la firma digital
        if (! count($errors)) {
            // signature
            $hashedValue = $request['signature'];
            unset($request['signature']);
            // construimos de nuevo la firma
            $signature = $this->arrayToSignature($request);
            $hashedExpected = hash_hmac('sha256', $signature, $this->keys['snt']);
            if (! hash_equals($hashedExpected, $hashedValue)) {
                $errors[] = 'No se puede verificar la identidad del emisor';
            }
        }
        return new ValidateResultado($request, $errors);
    }

    /**
     * función recursiva que permite recorrer un arreglo y pasarlo a una cadena
     * @param array $arreglo arrteglo, normalmente el request
     * @return string
     */
    function arrayToSignature($arreglo)
    {
        $cadena = '';
        ksort($arreglo);
        foreach ($arreglo as $key => $value) {
            if (is_array($value)) {
                if (count($value) != count($value, COUNT_RECURSIVE)){
                    $value = $this->arrayToSignature($value);
                }else{
                    ksort($value);
                    $value = implode(':', $value);
                }
            }
            $cadena .= $key . '+' . $value;
        }
        return $cadena;
    }
}
