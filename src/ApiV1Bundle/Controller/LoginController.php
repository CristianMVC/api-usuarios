<?php
namespace ApiV1Bundle\Controller;

use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\Controller\Annotations\Post;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class DefaultController
 *
 * @package ApiV1Bundle\Controller
 * @author Fausto Carrera <fcarrera@hexacta.com>
 */
class LoginController extends ApiController
{

    /**
     * Login users
     *
     * @param Request $request
     * @Post("/auth/login")
     */
    public function loginUser(Request $request)
    {
        $params = $request->request->all();
        $securityServices = $this->getLoginServices();
        return $securityServices->login(
            $params,
            function ($response, $flush) {
                return $response;
            },
            function ($err) {
                return $this->respuestaForbidden($err);
            }
        );
    }
}
