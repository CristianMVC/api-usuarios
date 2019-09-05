<?php

namespace ApiV1Bundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * Class ControllerTestCase
 * @package ApiV1Bundle\Tests\Controller
 */
class ControllerTestCase extends WebTestCase
{
    private $container;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        self::bootKernel();
        $this->container = static::$kernel->getContainer();
    }

    /**
     * {@inheritDoc}
     */
    public static function tearDownAfterClass()
    {
    }

    /**
     * Obtenemos el contenedor
     * @return object
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * generateRandomString
     * función que se utiliza para generar strings aleatorios para usarlos como parte del nombre de usuario.
     * @param int $length
     *
     * @return string
     */
    protected function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

}

