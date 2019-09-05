<?php
namespace ApiV1Bundle\Tests\Controller;

/**
 * Class UsuarioControllerTest
 *
 * @package ApiV1Bundle\Tests\Controller
 */
class SecurityControllerTest extends ControllerTestCase
{
    public function testLogin()
    {
        $client = static::createClient();
        $client->followRedirects();
        $params = [
            'username' => 'test@test.com',
            'password' => 'test'
        ];
        $client->request('POST', '/api/v1.0/auth/login', $params);
        // content
        $content = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertTrue(array_key_exists('id', $content));
        $this->assertTrue(array_key_exists('username', $content));
        $this->assertTrue(array_key_exists('token', $content));
        $this->assertTrue(array_key_exists('organismo', $content));
        $this->assertTrue(array_key_exists('area', $content));
        $this->assertTrue(array_key_exists('puntoAtencion', $content));
        $this->assertTrue(array_key_exists('rol', $content));
        return $content['token'];
    }

    /**
     * Test logout user
     * @depends testLogin
     */
    public function testLogout($token)
    {
        $client = static::createClient();
        $client->followRedirects();
        $headers = [
            'HTTP_AUTHORIZATION' => "Bearer {$token}",
            'CONTENT_TYPE' => 'application/json'
        ];
        $client->request('POST', '/api/v1.0/auth/logout', [], [], $headers);
        $content = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertTrue(array_key_exists('status', $content));
        $this->assertEquals('SUCCESS', $content['status']);
        return $token;
    }

}