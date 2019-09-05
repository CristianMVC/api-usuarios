<?php
namespace ApiV1Bundle\Tests\Controller;

/**
 * Class UsuarioControllerTest
 *
 * @package ApiV1Bundle\Tests\Controller
 */
class UsuarioControllerTest extends ControllerTestCase
{

    /** @var Id unico de usuario en SNU */
    private $userId;

    /**
     * Recibe un nombre de usuario, obtiene un JWT y devuelve el arreglo de
     * headers
     *
     * @param $username
     * @return array
     */
    private function getHeaders($username)
    {
        $client = static::createClient();
        $client->followRedirects();
        $params = [
            'username' => $username,
            'password' => 'test'
        ];
        $client->request('POST', '/api/v1.0/auth/login', $params);
        $data = json_decode($client->getResponse()->getContent(), true);
        return [
            'HTTP_AUTHORIZATION' => "Bearer {$data['token']}"
        ];
    }

    /**
     * testAdminGetListadoAction
     */
    public function testAdminGetListadoAction()
    {/*
        $headers = $this->getHeaders('test@test.com');
        $client = static::createClient();
        $client->followRedirects();
        $client->request('GET', '/api/v1.0/usuarios', [], [], $headers);
        $data = json_decode($client->getResponse()->getContent(), true);
        $usuario = json_decode($client->getResponse()->getContent())->result[0];
        self::assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertTrue(property_exists($usuario, 'id'));
        $this->assertTrue(property_exists($usuario, 'nombre'));
        $this->assertTrue(property_exists($usuario, 'apellido'));
        $this->assertTrue(property_exists($usuario, 'rol'));
        $this->assertTrue(property_exists($usuario, 'puntoAtencion'));
    */
    }

    /**
     * testPostAdminAction
     * Test automatizado para testear la creación un nuevo admin
     * Endpoint: /usuarios
     * return int
     */
    public function testPostAdminAction()
    {
        $client = static::createClient();
        $client->followRedirects();
        $params = [
            'nombre' => 'Juan AdminTest',
            'apellido' => 'Albornoz',
            'username' => $this->generateRandomString() . '@nomail.com.ar',
            'rol' => 1
        ];

        $client->request('POST', '/api/v1.0/usuarios', $params);
        $nuevoUsuario = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(200, $client->getResponse()
            ->getStatusCode());
        return $nuevoUsuario['additional']['id'];
    }

    /**
     * testPostOrganismoAction
     * Test automatizado para testear la creación un nuevo resp organismo
     * Endpoint: /usuarios
     * return int
     */
    public function testPostOrganismoAction()
    {
        $client = static::createClient();
        $client->followRedirects();
        $params = [
            'nombre' => 'Fernando Organismo Test',
            'apellido' => 'Biale',
            'username' => $this->generateRandomString() . '@nomail.com.ar',
            'rol' => 2,
            'organismo' => 1
        ];

        $client->request('POST', '/api/v1.0/usuarios', $params);
        $nuevoUsuario = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(200, $client->getResponse()
            ->getStatusCode());
        return $nuevoUsuario['additional']['id'];
    }

    /**
     * testPostAreaAction
     * Test automatizado para testear la creación un nuevo resp area
     * Endpoint: /usuarios
     * return int
     */
    public function testPostAreaAction()
    {
        $client = static::createClient();
        $client->followRedirects();
        $params = [
            'nombre' => 'Jose Area Test',
            'apellido' => 'Pereda',
            'username' => $this->generateRandomString() . '@nomail.com.ar',
            'rol' => 3,
            'organismo' => 1,
            'area' => 1
        ];

        $client->request('POST', '/api/v1.0/usuarios', $params);
        $nuevoUsuario = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(200, $client->getResponse()
            ->getStatusCode());
        return $nuevoUsuario['additional']['id'];
    }

    /**
     * testPostAreaAction
     * Test automatizado para testear la creación un nuevo resp area
     * Endpoint: /usuarios
     * return int
     */
    public function testPostPuntoAtencionAction()
    {
        $client = static::createClient();
        $client->followRedirects();
        $params = [
            'nombre' => 'Gustavo PDA Test',
            'apellido' => 'Zalazar',
            'username' => $this->generateRandomString() . '@nomail.com.ar',
            'rol' => 4,
            'organismo' => 1,
            'area' => 1,
            'puntoAtencion' => 29
        ];

        $client->request('POST', '/api/v1.0/usuarios', $params);
        $nuevoUsuario = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(200, $client->getResponse()
            ->getStatusCode());
        return $nuevoUsuario['additional']['id'];
    }

    /**
     * testPostAreaAction
     * Test automatizado para testear la creación un nuevo agente
     * Endpoint: /usuarios
     * return int
     */
    public function testPostAgenteAction()
    {
        $client = static::createClient();
        $client->followRedirects();
        $params = [
            'nombre' => 'Mr. Anderson Agente Test',
            'apellido' => 'Test Lastname',
            'username' => $this->generateRandomString() . '@nomail.com.ar',
            'rol' => 5,
            'puntoAtencion' => 1,
            'ventanillas' => [
                1,
                2
            ]
        ];

        $client->request('POST', '/api/v1.0/usuarios', $params);
        $nuevoUsuario = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(200, $client->getResponse()
            ->getStatusCode());
        return $nuevoUsuario['additional']['id'];
    }

    /**
     * test ObtenerUsuario
     * Test automatizado para probar el obtener los datos de un usuario
     * Endpoint: /usuarios/{idUser}
     * * @depends testPostAgenteAction
     */
    public function testObtenerUsuario($id)
    {
        $client = static::createClient();
        $client->followRedirects();
        $client->request('GET', '/api/v1.0/usuarios/1/rol/1');

        $this->assertEquals(200, $client->getResponse()
            ->getStatusCode());
    }

    /**
     * testPutModificarAdminAction
     * Test automatizado para comprobar la modificación de un usuario admin previamente creado
     * Endpoint: /usuarios/{idUser}
     * @depends testPostAdminAction
     */
    public function testPutModificarAdminAction($id)
    {
        $this->userId = $id;
        $client = static::createClient();
        $client->followRedirects();
        $params = [
            "nombre" => "Juan AdminTest",
            "apellido" => "put",
            'username' => $this->generateRandomString() . '@nomail.com.ar',
            "rol" => 1
        ];
        $client->request('PUT', '/api/v1.0/usuarios/' . $this->userId . '/rol/1', $params);
        $this->assertEquals(200, $client->getResponse()
            ->getStatusCode());
    }

    /**
     * testPutModificarOrganismoAction
     * Test automatizado para comprobar la modificación de un usuario organismo previamente creado
     * Endpoint: /usuarios/{idUser}
     * @depends testPostOrganismoAction
     */
    public function testPutModificarOrganismoAction($id)
    {
        $this->userId = $id;
        $client = static::createClient();
        $client->followRedirects();
        $params = [
            "nombre" => "Fernando Organismo Test",
            "apellido" => "put",
            'username' => $this->generateRandomString() . '@nomail.com.ar',
            "rol" => 2,
            "organismo" => 1
        ];
        $client->request('PUT', '/api/v1.0/usuarios/' . $this->userId . '/rol/2', $params);
        $this->assertEquals(200, $client->getResponse()
            ->getStatusCode());
    }

    /**
     * testPutModificarAreaAction
     * Test automatizado para comprobar la modificación de un usuario area previamente creado
     * Endpoint: /usuarios/{idUser}
     * @depends testPostAreaAction
     */
    public function testPutModificarAreaAction($id)
    {
        $this->userId = $id;
        $client = static::createClient();
        $client->followRedirects();
        $params = [
            "nombre" => "Jose Area Test",
            "apellido" => "put",
            'username' => $this->generateRandomString() . '@nomail.com.ar',
            "rol" => 3,
            "area" => 3,
            "organismo" => 1
        ];
        $client->request('PUT', '/api/v1.0/usuarios/' . $this->userId . '/rol/3', $params);
        $this->assertEquals(200, $client->getResponse()
            ->getStatusCode());
    }

    /**
     * testPutModificarPuntoAtencionAction
     * Test automatizado para comprobar la modificación de un usuario pda previamente creado
     * Endpoint: /usuarios/{idUser}
     * @depends testPostPuntoAtencionAction
     */
    public function testPutModificarPuntoAtencionAction($id)
    {
        $this->userId = $id;
        $client = static::createClient();
        $client->followRedirects();
        $params = [
            "nombre" => "Gustavo PDA Test",
            "apellido" => "put",
            'username' => $this->generateRandomString() . '@nomail.com.ar',
            "rol" => 4,
            "area" => 3,
            "puntoAtencion" => 1
        ];
        $client->request('PUT', '/api/v1.0/usuarios/' . $this->userId . '/rol/4', $params);
        $this->assertEquals(200, $client->getResponse()
            ->getStatusCode());
    }

    /**
     * testPutModificarAgenteAction
     * Test automatizado para comprobar la modificación de un usuario agente previamente creado
     * Endpoint: /usuarios/{idUser}
     * @depends testPostAgenteAction
     */
    public function testPutModificarAgenteAction($id)
    {
        $this->userId = $id;
        $client = static::createClient();
        $client->followRedirects();
        $params = [
            "nombre" => "Mr. Anderson Agente Test",
            "apellido" => "put",
            'username' => $this->generateRandomString() . '@nomail.com.ar',
            "rol" => 5,
            "puntoAtencion" => 1,
            "ventanillas" => [
                181,
                99
            ]
        ];
        $client->request('PUT', '/api/v1.0/usuarios/' . $this->userId . '/rol/5', $params);
        $this->assertEquals(200, $client->getResponse()
            ->getStatusCode());
    }

    /**
     * test EliminarUsuarioAction
     * Test automatizado para testear el borrado de usuarios
     * Endpoint: /usuarios/{idUser}/rol/{rolId}
     *
     * @param $userId
     * @depends testPostAgenteAction
     */
    public function testEliminarUsuarioAction($id)
    {
        $this->userId = $id;
        $client = static::createClient();
        $client->followRedirects();
        $client->request('DELETE', '/api/v1.0/usuarios/' . $this->userId . '/rol/5');
        $this->assertEquals(200, $client->getResponse()
            ->getStatusCode());
        // debe fallar si tratamos de borrar el mismo usuario
        $client->request('DELETE', '/api/v1.0/usuarios/' . $this->userId . '/rol/5');
        $this->assertEquals(400, $client->getResponse()
            ->getStatusCode());
    }

    /**
     * test EliminarUsuarioadminAction
     * Test automatizado para testear el borrado de usuarios
     * Endpoint: /usuarios/{idUser}/rol/{rolId}
     *
     * @param $userId
     * @depends testPostAdminAction
     */
    public function testEliminarUsuarioAdminAction($id)
    {
        $this->userId = $id;
        $client = static::createClient();
        $client->followRedirects();
        $client->request('DELETE', '/api/v1.0/usuarios/' . $this->userId . '/rol/1');
        $this->assertEquals(200, $client->getResponse()
            ->getStatusCode());
        // debe fallar si tratamos de borrar el mismo usuario
        $client->request('DELETE', '/api/v1.0/usuarios/' . $this->userId . '/rol/1');
        $this->assertEquals(400, $client->getResponse()
            ->getStatusCode());
    }

    /**
     * test EliminarUsuarioOrganismoAction
     * Test automatizado para testear el borrado de usuarios
     * Endpoint: /usuarios/{idUser}/rol/{rolId}
     *
     * @param $userId
     * @depends testPostOrganismoAction
     */
    public function testEliminarUsuarioOrganismoAction($id)
    {
        $this->userId = $id;
        $client = static::createClient();
        $client->followRedirects();
        $client->request('DELETE', '/api/v1.0/usuarios/' . $this->userId . '/rol/2');
        $this->assertEquals(200, $client->getResponse()
            ->getStatusCode());
        // debe fallar si tratamos de borrar el mismo usuario
        $client->request('DELETE', '/api/v1.0/usuarios/' . $this->userId . '/rol/2');
        $this->assertEquals(400, $client->getResponse()
            ->getStatusCode());
    }

    /**
     * test EliminarUsuarioAreaAction
     * Test automatizado para testear el borrado de usuarios
     * Endpoint: /usuarios/{idUser}/rol/{rolId}
     *
     * @param $userId
     * @depends testPostAreaAction
     */
    public function testEliminarUsuarioAreaAction($id)
    {
        $this->userId = $id;
        $client = static::createClient();
        $client->followRedirects();
        $client->request('DELETE', '/api/v1.0/usuarios/' . $this->userId . '/rol/3');
        $this->assertEquals(200, $client->getResponse()
            ->getStatusCode());
        // debe fallar si tratamos de borrar el mismo usuario
        $client->request('DELETE', '/api/v1.0/usuarios/' . $this->userId . '/rol/3');
        $this->assertEquals(400, $client->getResponse()
            ->getStatusCode());
    }

    /**
     * test EliminarUsuarioPuntoAtencionAction
     * Test automatizado para testear el borrado de usuarios
     * Endpoint: /usuarios/{idUser}/rol/{rolId}
     *
     * @param $userId
     * @depends testPostPuntoAtencionAction
     */
    public function testEliminarUsuarioPuntoAtencionAction($id)
    {
        $this->userId = $id;
        $client = static::createClient();
        $client->followRedirects();
        $client->request('DELETE', '/api/v1.0/usuarios/' . $this->userId . '/rol/4');
        $this->assertEquals(200, $client->getResponse()
            ->getStatusCode());
        // debe fallar si tratamos de borrar el mismo usuario
        $client->request('DELETE', '/api/v1.0/usuarios/' . $this->userId . '/rol/4');
        $this->assertEquals(400, $client->getResponse()
            ->getStatusCode());
    }
}
