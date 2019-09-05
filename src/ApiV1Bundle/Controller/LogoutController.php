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
class LogoutController extends ApiController
{

    /**
     * Logout users
     *
     * @param Request $request
     * @Post("/auth/logout")
     */
    public function logoutUser(Request $request)
    {
        $token = $request->headers->get('authorization', null);
        $securityServices = $this->getLogoutServices();

        return $securityServices->logout(
            $token,
            function ($flush) {
                return $this->respuestaOk('Sesion terminada');
            }
        );
    }
}
