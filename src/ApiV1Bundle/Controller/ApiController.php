<?php
namespace ApiV1Bundle\Controller;

use ApiV1Bundle\ApplicationServices\ContrasenaServices;
use ApiV1Bundle\Entity\Response\Respuesta;
use ApiV1Bundle\Entity\Response\RespuestaConEstado;
use FOS\RestBundle\Controller\FOSRestController;

/**
 * Class ApiController
 *
 * Clase base de los controladores de la API
 * @author Fausto Carrera <fcarrera@hexacta.com>
 *
 * @package ApiV1Bundle\Controller
 */

class ApiController extends FOSRestController
{
    /**
     * Symfony logger
     *
     * @return object
     */
    protected function getLogger()
    {
        return $this->container->get('logger');
    }

    /**
     * Get parameter
     *
     * {@inheritDoc}
     * @see \Symfony\Bundle\FrameworkBundle\Controller\Controller::getParameter()
     */
    protected function getParameter($name)
    {
        return $this->container->getParameter($name);
    }

    /**
     * Obtiene el Security Service
     *
     * @return object
     */
    protected function getLoginServices()
    {
        return $this->container->get('user.services.login');
    }

    /**
     * @return ContrasenaServices
     */
    public function getContrasenaServices()
    {
        return $this->container->get('user.services.contrasena');
    }

    /**
     * Obtiene el Security Service
     *
     * @return object
     */
    protected function getLogoutServices()
    {
        return $this->container->get('user.services.logout');
    }

    /**
     * Obtiene Usuario service
     *
     * @return object
     */
    protected function getUsuarioServices()
    {
        return $this->container->get('user.services.usuario');
    }

    /**
     * Obtiene Punto de atención service
     *
     * @return object
     */
    protected function getPuntoAtencionServices()
    {
        return $this->container->get('user.services.puntoatencion');
    }

    /**
     * Retorna una Respuesta con estado SUCCESS
     *
     * @param string $message Mensaje de éxito
     * @return RespuestaConEstado
     */
    protected function respuestaOk($message, $additional = '')
    {
        return new RespuestaConEstado(
            RespuestaConEstado::STATUS_SUCCESS,
            RespuestaConEstado::CODE_SUCCESS,
            $message,
            '',
            $additional
        );
    }

    /**
     * Retorna una Respuesta con estado FATAL
     *
     * @param string $message Mensaje Fatal
     * @return RespuestaConEstado
     */
    protected function respuestaError($message)
    {
        return new RespuestaConEstado(
            RespuestaConEstado::STATUS_FATAL,
            RespuestaConEstado::CODE_FATAL,
            $message,
            '',
            ''
        );
    }

    /**
     * Retorna una Respuesta con estado Not Found
     *
     * @param string $message Mensaje No encontrado
     * @return RespuestaConEstado
     */
    protected function respuestaNotFound($message)
    {
        return new RespuestaConEstado(
            RespuestaConEstado::STATUS_NOT_FOUND,
            RespuestaConEstado::CODE_NOT_FOUND,
            $message
        );
    }

    /**
     * Retorna una Respuesta con estado Bad Request
     *
     * @param string $message Mensaje respuesta errónea
     * @return RespuestaConEstado
     */
    protected function respuestaBadRequest($message)
    {
        return new RespuestaConEstado(
            RespuestaConEstado::STATUS_BAD_REQUEST,
            RespuestaConEstado::CODE_BAD_REQUEST,
            $message
        );
    }

    /**
     * Retorna una respuesta con estado Forbidden
     *
     * @param string $message
     * @return RespuestaConEstado
     */
    protected function respuestaForbidden($message)
    {
        return new RespuestaConEstado(
            RespuestaConEstado::STATUS_FORBIDDEN,
            RespuestaConEstado::CODE_FORBIDDEN,
            $message
        );
    }

    /**
     * Retorna una Respuesta con datos
     *
     * @param $metadata
     * @param $result
     * @return \ApiV1Bundle\Entity\Respuesta
     */
    protected function respuestaData($metadata, $result)
    {
        return new Respuesta($metadata, $result);
    }
}
