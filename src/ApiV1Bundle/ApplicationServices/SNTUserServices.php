<?php

namespace ApiV1Bundle\ApplicationServices;

use ApiV1Bundle\Entity\Factory\UsuarioFactory;
use ApiV1Bundle\Entity\Response\Respuesta;
use ApiV1Bundle\Entity\ValidateResultado;
use ApiV1Bundle\ExternalServices\SecurityIntegration;
use Symfony\Component\DependencyInjection\Container;

/**
 * Class SNTUserServices
 * @package ApiV1Bundle\ApplicationServices
 */

class SNTUserServices
{
    private $logger;
    private $container;

    /**
     * SNTServices constructor.
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->logger = $this->container->get('logger');
    }

    /**
     * Obtenemos uno de los parametros de la configuración
     *
     * @param $parameter
     * @return mixed
     */
    public function getParameter($parameter)
    {
        return $this->container->getParameter($parameter);
    }

    /**
     * Retorna la cantidad de errores que se produjeron
     *
     * @param array $errors Array con los errores que se produjeron
     * @return int
     */
    protected function hasErrors($errors)
    {
        return (count($errors['errors']));
    }

    /**
     * Valida el resutlado del proceso
     *
     * @param object $validateResult Objeto a validar
     * @param $onSucess | Si tuvo éxito
     * @param $onError | Si se produjo un error
     * @return mixed
     */
    protected function processResult($validateResult, $onSucess, $onError)
    {
        if ($validateResult->hasError()) {
            return call_user_func($onError, $validateResult->getErrors());
        } else {
            $errors = $this->validate($validateResult->getEntity());
            if ($this->hasErrors($errors)) {
                return call_user_func($onError, $errors);
            } else {
                return call_user_func($onSucess, $validateResult->getEntity());
            }
        }
    }

    /**
     * Procesa los errores
     *
     * @param $validateResult
     * @param $onSucess
     * @param $onError
     * @return mixed
     */
    protected function processError($validateResult, $onSucess, $onError)
    {
        if ($validateResult->hasError()) {
            return call_user_func($onError, $validateResult->getErrors());
        }
        return call_user_func($onSucess);
    }

    /**
     * Devuelve un objeto respuesta
     *
     * @param $metadata
     * @param $result
     * @return object Respuesta
     */
    protected function respuestaData($metadata, $result)
    {
        return new Respuesta($metadata, $result);
    }

    /**
     * Valida una entidad que recibe por parámetro
     *
     * @param object $entity Objeto entidad
     * @return array
     */
    protected function validate($entity)
    {
        $response = [
            'errors' => []
        ];
        $errors = $this->container->get('validator')->validate($entity);

        if (count($errors)) {
            foreach ($errors as $error) {
                $response['errors'][$error->getPropertyPath()] = $error->getMessage();
            }
        }
        return $response;
    }

}
