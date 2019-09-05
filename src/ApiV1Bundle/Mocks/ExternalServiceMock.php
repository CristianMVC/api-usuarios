<?php
namespace ApiV1Bundle\Mocks;

use Symfony\Component\DependencyInjection\Container;
use ApiV1Bundle\Entity\ValidateResultado;

/**
 * Class SNTExternalServiceMock
 * @package ApiV1Bundle\Mocks
 */
class ExternalServiceMock
{
    /** @var null $host  */
    private $host = [];

    /** @var array $urls */
    private $urls = [];

    /** @var array $apiId */
    private $apiId = [];

    /** @var array $keys */
    private $keys = [];

    /**
     * SNTExternalServiceMock constructor
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $config = $container->getParameter('integration');
        $this->host = $config['host'];
        $this->urls = $config['urls'];
        $this->apiId = $config['api_id'];
        $this->keys = $config['keys'];
    }

    /**
     * Mock get
     *
     * @param string $url
     * @return stdClass
     */
    public function get($url, $parameters = null)
    {
        $urlParts = parse_url($url);
        switch ($urlParts['path']) {
            case '/api/v1.0/turnos/2':
                $response = $this->getTurno();
                break;
            case '/api/v1.0/usuarios/1':
                $response = $this->getUsuario();
                break;
            case '/api/v1.0/usuarios':
                $response = $this->listadoUsuarios();
                break;
            //case '/api/v1.0/puntosatencion':
                $response = $this->getObtenerPuntosAtencion();
                break;
            default:
                $response = $this->getResponse($parameters);
        }
        return $response;
    }

    /**
     * Mock post
     *
     * @param string $url
     * @param $body
     * @return mixed
     */
    public function post($url, $body = null, $header = null)
    {
        $urlParts = parse_url($url);
        switch ($urlParts['path']) {
            case '/api/v1.0/integracion/turnos/fecha':
                $response = $this->getPuntosAtencionByFecha();
                break;
            case '/api/v1.0/usuarios':
                $response = $this->crearUsuario();
                break;
            case '/api/v1.0/auth/login':
                $response = ($body['username'] == 'admin@test.com')
                    ? $this->getLoginUsuarioAdmin()
                    : $this->getLoginUsuarioPDA();
                break;
            case '/api/v1.0/auth/logout':
                $response = $this->getLogoutUsuario();
                break;
            default:
                $response = $this->getResponse($body);
        }
        return $response;
    }

    /**
     * Mock put
     *
     * @param string $url
     * @param $body
     * @return mixed
     */
    public function put($url, $body = null, $header = null)
    {
        $urlParts = parse_url($url);
        if (preg_match("/^\/api\/v1.0\/usuarios\/\d+/", $urlParts['path'])) {
            return $this->getModificarUsuario();
        }

        return $this->getResponse($body);
    }

    /**
     * Mock delete
     *
     * @param string  $url
     * @param $body
     * @return mixed
     */
    public function delete($url, $body = null, $header = null)
    {
        $urlParts = parse_url($url);
        if (preg_match("/^\/api\/v1.0\/usuarios\/\d+/", $urlParts['path'])) {
            return $this->getModificarUsuario();
        }
        return $this->getResponse($body);
    }

    /**
     * Componer una url
     *
     * @param string $name
     * @param string $additional
     * @return NULL|string
     */
    public function getUrl($system, $name, $additional = null, $params = null)
    {
        $url = null;
        if (isset($this->urls[$system][$name])) {
            $url = $this->host[$system] . $this->urls[$system][$name];
        }
        if ($url && $additional) {
            if (substr($url, -1) !== '/') {
                $url .= '/';
            }
            $url .= $additional;
        }
        if ($url) {
            $params = $this->getSignedBody($params, false);
        }
        if ($url && $params) {
            if (strpos($url, '?') !== false) {
                $url .= '&';
            } else {
                $url .= '?';
            }
            $url .= http_build_query($params);
        }
        return $url;
    }

    /**
     * Get test URL
     *
     * @param $url
     * @param $additional
     * @param $params
     * @return string
     */
    public function getTestUrl($url, $additional = null, $params = null)
    {
        if ($url && $additional) {
            if (substr($url, -1) !== '/') {
                $url .= '/';
            }
            $url .= $additional;
        }
        if ($url) {
            $params = $this->getSignedBody($params, false);
        }
        if ($url && $params) {
            if (strpos($url, '?') !== false) {
                $url .= '&';
            } else {
                $url .= '?';
            }
            $url .= http_build_query($params);
        }
        return $url;
    }

    /**
     * Return signed body for test purpose
     * @param $body
     * @return string
     */
    public function getTestSignedBody($body, $asObject = true)
    {
        return $this->getSignedBody($body, $asObject);
    }

    /**
     * Response con objeto mock
     *
     * @return \stdClass
     */
    private function getResponse($body)
    {
        $response = new \stdClass();
        $response->code = 200;
        $response->body = $this->getSignedBody($body);
        return $response;
    }

    /**
     * Obtenemos el cuerpo del mensaje firmado
     *
     * @param $body
     * @return string
     */
    private function getSignedBody($body = null, $asObject = true)
    {
        if (! $body || ! is_array($body)) {
            $body = [];
        }
        $body['api_id'] = $this->apiId['snt'];
        $body['signature'] = $this->sign($body);
        if ($asObject) {
            $body = (object) $body;
        }
        return $body;
    }

    /**
     * Firma digitalmente un request
     *
     * @param array $request
     * @return string
     */
    private function sign($request)
    {
        $signature = '';
        ksort($request);
        foreach ($request as $key => $value) {
            if (is_array($value)) {
                ksort($value);
                $value = implode(':', $value);
            }
            $signature .= $key . '+' . $value;
        }
        return hash_hmac('sha256', $signature, $this->keys['snt']);
    }

    private function listadoUsuarios(){
        $r =  json_decode('{
                "metadata": "resultset": {
                    "count": 2,
                    "offset": 0,
                    "limit": 10
                },
                "result":{ 
                    {
                        "id": 1,
                        "nombre": "Test",
                        "apellido": "Test",
                        "usuario": "test",
                        "rol": 1,
                        "organismo": {
                            "id": null,
                            "nombre": null
                        },
                        "area": {
                            "id": null,
                            "nombre": null
                        },
                        "puntoAtencion": {
                            "id": null,
                            "nombre": null
                        }
                    },
                    {
                        "id": 2,
                        "nombre": "Test",
                        "apellido": "Test",
                        "usuario": "test@test.com",
                        "rol": 1,
                        "organismo": {
                            "id": null,
                            "nombre": null
                        },
                        "area": {
                            "id": null,
                            "nombre": null
                        },
                        "puntoAtencion": {
                            "id": null,
                            "nombre": null
                        }
                    }
                }            
        }', true);
        return new ValidateResultado($r,[]);

    }
    /**
     * Devuelve un turno
     *
     * @return mixed
     */
    private function getTurno()
    {
        $response = json_decode('{
            "metadata": [],
            "result": {
                "id": 1,
                "codigo": "c2a107a9-cd88-45f5-b382-c058f8e5e6d5",
                "punto_atencion": {
                    "id": 1,
                    "nombre": "pda::ANSES::005::023",
                    "direccion": "Calle falsa 123",
                    "latitud": -34.6033,
                    "longitud": -58.3816
                },
                "alerta": 2,
                "fecha": "2017-09-12",
                "hora": "16:26",
                "tramite": {
                    "id": 29,
                    "nombre": "Morbi ornare ligula id mauris luctus"
                },
                "grupo_tramite": {
                    "id": 38
                },
                "datos_turno": {
                    "nombre": "Juan",
                    "apellido": "Perez",
                    "cuil": "20469731767",
                    "email": "nowhere@example.com",
                    "telefono": "123456",
                    "campos": {
                        "nombre": "Dar?o",
                        "apellido": "Cvitanich",
                        "sexo": "radio3",
                        "cuil": "23-28423371-9",
                        "email": "fernandomviale@hotmail.com",
                        "telefono": "1554926448"
                    }
                },
                "estado": 1,
                "area": {
                    "id": 15,
                    "nombre": "ANSES::005",
                    "abreviatura": "7BD"
                }
            }
        }');
        $response->code = 200;
        return $response;
    }

    /*
     * Devuelve un listado de puntos de atención por fecha
     */
    private function getPuntosAtencionByFecha()
    {
        $response = json_decode('{
            "metadata": {
                "resultset": {
                    "count": 1,
                    "offset": 0,
                    "limit": 10
                }
            },
            "result": [
                {
                    "id": 1,
                    "punto_atencion": 1,
                    "campos": {
                        "nombre": "nombre",
                        "apellido": "apellido",
                        "cuil": "27-27104266-9",
                        "email": "e@mail.com",
                        "telefono": "1234"
                    },
                    "fecha": "2018-01-08T00:00:00-03:00",
                    "hora": "1970-01-01T13:00:00-03:00",
                    "estado": 1,
                    "tramite": "Morbi ornare ligula id mauris luctus",
                    "codigo": "22be6d17"
                }
            ]
        }');
        $response->code = 200;
        return $response;
    }

    /**
     * Devuelve la creación de un usuario
     * @return ValidateResultado
     */
    private function crearUsuario()
    {
        $r = json_decode('{
            "code": 200,
            "status": "SUCCESS",
            "userMessage": "Usuario creado con éxito",
            "devMessage" : "",
            "additional": {
                "id": ' . rand(0, 1000) . '
            }
        }',true);
        return new ValidateResultado($r,[]);
    }

    /**
     * Devuelve la obtención de un usuario
     * @return ValidateResultado
     */
    private function getUsuario()
    {
        return  json_decode('{
                "metadata": [],
                "result": {
                    "id": 91,
                    "nombre": "nombre Usuario",
                    "apellido": "apellido usuario",
                    "username": "usuario@hxsnt.com",
                    "rol": 1,
                    "puntoAtencion": {
                        "id": 1,
                        "nombre": "SNR - Ramsay",
                        "snt_id": 1
                    }
                }
            }
        }',true);
    }

    /**
     * Devuelve la obtención de un usuario
     * @return array
     */
    private function getObtenerUsuario()
    {
        return  json_decode('{
                "metadata": [],
                "result": {
                    "id": 91,
                    "nombre": "nombreAgente",
                    "apellido": "apellidoAgente",
                    "username": "agente@hxsnt.com",
                    "rol": 3,
                    "puntoAtencion": {
                        "id": 1,
                        "nombre": "SNR - Ramsay",
                        "snt_id": 1
                    },
                    "ventanillas": [
                        {},
                        {}
                    ],
                    "ventanillaActual": 99
                }
            }
        }');
        return new ValidateResultado($response,[]);
    }

    /**
     * Devuelve el login de un usuario
     * @return array
     */
    private function getLoginUsuarioPDA()
    {
        $response = json_decode('{
        	"id": 1,
        	"username": "test@test.com",
        	"token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9zbnQuYXJnZW50aW5hLmdvYi5hciIsImF1ZCI6Imh0dHA6XC9cL3NudC5hcmdlbnRpbmEuZ29iLmFyIiwiaWF0IjoxNTE5MjQzMTE2LCJleHAiOjE1MTkyNTAzMTYsImp0aSI6IjlmNzRhN2RlZTllOTc1YjA2YjM1NjhhNTY0NmZlMWI0IiwidGltZXN0YW1wIjoxNTE5MjQzMTE2LCJ1aWQiOjE4LCJ1c2VybmFtZSI6InBlcGVAcGRhLmNvbSIsInJvbGUiOiJST0xfUFVOVE9BVEVOQ0lPTiJ9.zcUWjb21G1frOSKcg5F5z7hdDhoZwrc9-y1n6o_at0g",
        	"organismo": 1,
        	"area": 2,
        	"rol": "ROL_PUNTOATENCION",
        	"rol_id": 4,
        	"nombre": "pepe pda mock",
        	"apellido": "snt",
        	"puntoAtencion": {
        		"id": 16,
        		"nombre": "pda::AFIP::002::016"
        	}
        }',true);
        return new ValidateResultado($response,[]);
    }

    /**
     * Devuelve el login de un usuario
     * @return array
     */
    private function getLoginUsuarioAdmin()
    {
        $response = json_decode('{
        	"id": 2,
        	"username": "admin@test.com",
        	"token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9zbnQuYXJnZW50aW5hLmdvYi5hciIsImF1ZCI6Imh0dHA6XC9cL3NudC5hcmdlbnRpbmEuZ29iLmFyIiwiaWF0IjoxNTE5MjQzMTE2LCJleHAiOjE1MTkyNTAzMTYsImp0aSI6IjlmNzRhN2RlZTllOTc1YjA2YjM1NjhhNTY0NmZlMWI0IiwidGltZXN0YW1wIjoxNTE5MjQzMTE2LCJ1aWQiOjE4LCJ1c2VybmFtZSI6InBlcGVAcGRhLmNvbSIsInJvbGUiOiJST0xfUFVOVE9BVEVOQ0lPTiJ9.zcUWjb21G1frOSKcg5F5z7hdDhoZwrc9-y1n6o_at0g",
        	"organismo": null,
        	"area": null,
        	"rol": "ROL_ADMIN",
        	"rol_id": 1,
        	"nombre": "pepe Admin Mock",
        	"apellido": "snt Mock",
        	"puntoAtencion": null
        }',true);
        return new ValidateResultado($response,[]);
    }

    /**
     * Devuelve el logout de un usuario
     * @return array
     */
    private function getLogoutUsuario()
    {
        $response = json_decode('{
        	"code": 200,
        	"status": "SUCCESS",
        	"userMessage": "Sesion terminada",
        	"devMessage": "",
        	"additional": [
        		""
        	]
        }',true);
        return new ValidateResultado($response,[]);
    }

    private function getModificarUsuario()
    {
        $r= json_decode('{
            "code": 200,
            "status": "SUCCESS",
            "userMessage": "Usuario modificado con éxito",
            "devMessage": "",
            "additional": [
                ""
            ]
        }', true);
        return new ValidateResultado($r,[]);
    }
}
