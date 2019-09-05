<?php
namespace ApiV1Bundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use ApiV1Bundle\Entity\Validator\TokenValidator;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use ApiV1Bundle\Entity\Response\RespuestaConEstado;
use Symfony\Component\HttpKernel\Kernel;

class HeadersListener
{
    private $securityServices;
    private $routes = [];
    private $kernel;

    public function __construct(TokenValidator $TokenValidator, array $routes, Kernel $kernel)
    {
        $this->TokenValidator = $TokenValidator;
        $this->routes = $routes;
        $this->kernel = $kernel;
    }

    /**
     * Request
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        // validate options response
        $optionsResponse = $this->optionsResponse($request);
        if ($optionsResponse) {
            $event->setResponse($optionsResponse);
        }
        // validate token
        if ($this->kernel->getEnvironment() == 'prod') {
            $tokenResponse = $this->tokenValidationResponse(
                $request->getPathInfo(),
                $request->headers->get('authorization', null),
                $request->getMethod()
            );
            if ($tokenResponse) {
                $event->setResponse($tokenResponse);
            }
        }
    }

    /**
     * Response
     * @param FilterResponseEvent $event
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        $request = $event->getRequest();
        // validate options response
        $optionsResponse = $this->optionsResponse($request);
        if ($optionsResponse) {
            $event->setResponse($optionsResponse);
        }
    }

    /**
     * Si es un OPTIONS devolver una respuesta estandar
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response|NULL
     */
    private function optionsResponse(Request $request)
    {
        if ($request->getMethod() == 'OPTIONS') {
            $response = new Response();
            $response->headers->set('Access-Control-Allow-Origin', '*');
            $response->headers->set('Access-Control-Allow-Headers', 'X-Requested-With');
            return $response;
        }
        return null;
    }

    /**
     * Validamos el token
     *
     * @param Request $request
     * @throws AccessDeniedHttpException
     */
    private function tokenValidationResponse($pathInfo, $authorization, $method)
    {
        $path = $this->checkRoute($pathInfo, $this->routes, $method);
        if ($path) {
            $roles = $this->routes[$path]['roles'];
            $token = $this->TokenValidator->validarAuthorization($authorization);
            if (! $token->isValid() || ! in_array($token->getRol(), $roles)) {
                return new RespuestaConEstado(
                    RespuestaConEstado::STATUS_FORBIDDEN,
                    RespuestaConEstado::CODE_FORBIDDEN,
                    'Forbidden'
                );
            }
        }
        return null;
    }

    /**
     * Validamos que la url del request empiece con alguna de las urls
     * que están en la lista de rutas que necesitan password
     *
     * @param $pathInfo
     * @param $routes
     * @return boolean
     */
    private function checkRoute($pathInfo, $routes, $method)
    {
        foreach ($routes as $path => $data) {
            $subroute = substr($pathInfo, 0, strlen($path));
            if ($subroute == $path) {
                if (! in_array($method, $data['metodos'])) {
                    return false;
                }
                return $path;
            }
        }
        return false;
    }
}
