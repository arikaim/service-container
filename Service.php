<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
 */
namespace Arikaim\Core\Service;

use Psr\Container\ContainerInterface;

use Arikaim\Core\Service\ServiceInterface;
use Arikaim\Core\Service\Traits\ServiceTrait;

/**
 *  Service base class
 */
class Service implements ServiceInterface
{    
    use ServiceTrait;

    /**
     * Included services
     *
     * @var ContainerInterface|null
     */
    protected $container = null;

    /**
     * Constructor
     * 
     * @param ContainerInterface|null $container 
     */
    public function __construct(?ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Get service instance
     *
     * @param string $name
     * @return mixed|null
     */
    public function getService(string $name)
    { 
        if (\is_null($this->container) == true) {
            return null;
        }

        return $this->container->get($name);
    }

    /**
     * Check for service
     *
     * @param string $name
     * @return bool
     */
    public function hasService(string $name): bool
    { 
        if (\is_null($this->container) == true) {
            return null;
        }

        return $this->container->has($name);
    }
}
