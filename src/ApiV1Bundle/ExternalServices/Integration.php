<?php
/**
 * Integration class
 * @author Fausto Carrera <fcarrera@hexacta.com>
 */
namespace ApiV1Bundle\ExternalServices;

use Symfony\Component\DependencyInjection\Container;

/**
 * Class Integration
 * @package ApiV1Bundle\ExternalServices
 */
class Integration
{
    /** @var Container  $container*/
    private $container;

    /**
     * Integration constructor
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Obtiene el objeto container
     *
     * @return \Symfony\Component\DependencyInjection\Container
     */
    protected function getContainer()
    {
        return $this->container;
    }

    /**
     * Obtiene el ambiente en que corre la aplicación
     *
     * @return string
     */
    protected function getEnvironment()
    {
        return $this->container->get('kernel')->getEnvironment();
    }


}
