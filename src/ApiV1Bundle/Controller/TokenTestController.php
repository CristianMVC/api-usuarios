<?php

namespace ApiV1Bundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Controller\Annotations\Delete;
use ApiV1Bundle\Entity\Response\Respuesta;

/**
 * Class DefaultController
 * @package ApiV1Bundle\Controller
 *
 * @author Fausto Carrera <fcarrera@hexacta.com>
 */

class TokenTestController extends ApiController
{

    /**
     * Test GET
     *
     * @return Respuesta
     * @Get("/token/test")
     */
    public function tokenGetAction()
    {
        return $this->respuestaData(null, [
            'status' => 'ok',
            'method' => 'GET',
        ]);
    }

    /**
     * Test POST
     *
     * @return Respuesta
     * @Post("/token/test")
     */
    public function tokenPostAction()
    {
        return $this->respuestaData(null, [
            'status' => 'ok',
            'method' => 'POST',
        ]);
    }

    /**
     * Test PUT
     *
     * @return Respuesta
     * @Put("/token/test")
     */
    public function tokenPutAction()
    {
        return $this->respuestaData(null, [
            'status' => 'ok',
            'method' => 'PUT',
        ]);
    }

    /**
     * Test DELETE
     *
     * @return Respuesta
     * @Delete("/token/test")
     */
    public function tokenDeleteAction()
    {
        return $this->respuestaData(null, [
            'status' => 'ok',
            'method' => 'DELETE',
        ]);
    }
}
