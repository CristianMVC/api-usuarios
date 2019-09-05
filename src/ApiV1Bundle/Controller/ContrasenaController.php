<?php

namespace ApiV1Bundle\Controller;

use FOS\RestBundle\Controller\Annotations\Post;
use Symfony\Component\HttpFoundation\Request;

class ContrasenaController extends ApiController
{
    /**
     * @param Request $request
     * @return mixed
     * @Post("auth/reset")
     */
    public function recuperarContrasena(Request $request)
    {
        $service = $this->getContrasenaServices();
        $params = $request->request->all();
        return $service->recuperarContrasena(
            $params,
            function ($resp) {
                return $this->respuestaOk($resp);
            },
            function ($err) {
                return $this->respuestaError($err);
            }
        );
    }

    /**
     * @param Request $request
     * @return mixed
     * @Post("auth/modificar")
     */
    public function modificarContrasena(Request $request)
    {
        $service = $this->getContrasenaServices();
        $params = $request->request->all();
        $authorization = $request->headers->get('authorization', null);
        return $service->modificarContrasena(
            $params,
            $authorization,
            function ($data) {
                return $this->respuestaOk($data);
            },
            function ($err) {
                return $this->respuestaError($err);
            }
        );
    }

    /**
     * @param Request $request
     * @return mixed
     * @Post("auth/validar")
     */
    public function validarToken(Request $request)
    {
        $token = $request->headers->get('authorization', null);
        $service = $this->getContrasenaServices();
        return $service->isTokenValid(
            $token,
            function ($data, $additional) {
                return $this->respuestaOk($data, $additional);
            },
            function ($err) {
                return $this->respuestaError($err);
            }
        );
    }
}
