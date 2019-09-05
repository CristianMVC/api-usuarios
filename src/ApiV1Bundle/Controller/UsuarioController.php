<?php

namespace ApiV1Bundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Controller\Annotations\Delete;
use Symfony\Component\HttpFoundation\Request;

class UsuarioController extends ApiController
{
    /** @var \ApiV1Bundle\ApplicationServices\UsuarioServices UsuarioServices*/
    private $usuarioServices;

    /**
     * Crea un usuario
     *
     * @param Request $request Se envian los datos para crear el usuario
     * @return mixed
     * @Post("/usuarios")
     */
    public function postAction(Request $request)
    {
        $params = $request->request->all();
        $usuarioServices = $this->getUsuarioServices();
        $authorization = $request->headers->get('authorization', null);

        return $usuarioServices->create(
            $params,
            $authorization,
            function ($usuario) {
                return $this->respuestaOk('Usuario creado con éxito', [
                    'id' => $usuario->getUserId()
                ]);
            },
            function ($err) {
                return $this->respuestaError($err);
            }
        );
    }

    /**
     * Obtiene un usuario
     *
     * @param integer $usuarioId Id de usuario en SNU
     * @return mixed
     * @Get("/usuarios/{usuarioId}/rol/{rolId}")
     *
     */
    public function getItemAction(Request $request, $usuarioId, $rolId)
    {
        $authorization = $request->headers->get('authorization', null);
        $this->usuarioServices = $this->getUsuarioServices();
        return $this->usuarioServices->get($usuarioId, $rolId, $authorization);
    }

    /**
     * Eliminar un usuario
     *
     * @param integer $id Identificador único del usuario en SNU
     * @return mixed
     * @Delete("/usuarios/{usuarioId}/rol/{rolId}")
     */
    public function deleteAction(Request $request, $usuarioId, $rolId)
    {
        $authorization = $request->headers->get('authorization', null);
        $this->usuarioServices = $this->getUsuarioServices();
        return $this->usuarioServices->delete(
            $usuarioId,
            $rolId,
            $authorization,
            function () {
                return $this->respuestaOk('Usuario eliminado con éxito');
            },
            function ($err) {
                return $this->respuestaError($err);
            }
        );
    }

    /**
     * Editar un usuario
     *
     * @param Request $request Espera el resultado de una petición como parámetro
     * @param integer $idUser Espera el id del usuario
     * @return mixed
     * @Put("/usuarios/{usuarioId}/rol/{rolId}")
     */
    public function putAction(Request $request, $usuarioId, $rolId)
    {
        $authorization = $request->headers->get('authorization', null);
        $params = $request->request->all();
        $this->usuarioServices = $this->getUsuarioServices();
        return $this->usuarioServices->edit(
            $params,
            $usuarioId,
            $rolId,
            $authorization,
            function () {
                return $this->respuestaOk('Usuario modificado con éxito');
            },
            function ($err) {
                return $this->respuestaError($err);
            }
        );
    }

    /**
     * Listado de usuarios
     *
     * @param Request $request Espera el resultado de una petición como parámetro
     * @return mixed
     * @Get("/usuarios")
     */
    public function getListAction(Request $request)
    {
        $authorization = $request->headers->get('authorization', null);
        $offset = $request->get('offset', 0);
        $limit = $request->get('limit', 10);
        $params = $request->query->all();
        $this->usuarioServices = $this->getUsuarioServices();
        return $this->usuarioServices->findAllPaginate(
            (int) $limit,
            (int) $offset,
            $authorization,
            function ($err) {
                return $this->respuestaForbidden($err);
            },
            $params
        );
    }

}