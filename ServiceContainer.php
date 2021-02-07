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

use Arikaim\Container\Container;
use Arikaim\Core\Utils\Path;
use Arikaim\Core\System\Traits\PhpConfigFile;
use Arikaim\Core\Service\ServiceInterface;
use Exception;

/**
 *  Service container
 */
class ServiceContainer
{
    use PhpConfigFile;

    /**
     *  Default providers config file name
     */
    const CONFIG_FILE_NAME = Path::CONFIG_PATH . 'service-providers.php';

    /**
     * Service container
     *
     * @var ContainerInterface
    */
    protected $container;

    /**
     * Service providers
     *
     * @var array|null
     */
    protected $serviceProviders = null;

    /**
     * Service providers config file
     *
     * @var string
     */
    private $configFileName;

    /**
     * Constructor
     * 
     * @param string|null $configFileName
     */
    public function __construct(?string $configFileName = null)
    {
        $this->container = new Container();
        $this->configFileName = $configFileName ?? Self::CONFIG_FILE_NAME;        
    }

    /**
     * Load service eproviders
     *
     * @param boolean $forceReload
     * @return void
     */
    public function load(bool $forceReload = false): void
    {
        if ((\is_null($this->serviceProviders) == false) && ($forceReload == false)) {
            return;
        }

        $this->serviceProviders = $this->include($this->configFileName);
    }

    /**
     * Get service providers
     *
     * @return array
     */
    public function getProviders(): array
    {
        $this->load();

        return $this->serviceProviders ?? [];
    }

    /**
     * Get provider class
     *
     * @param string $name
     * @return string|null
     */
    public function getProvider(string $name): ?string
    {
        $this->load();
        return $this->serviceProviders[$name] ?? null;
    }

    /**
     * Check if provider exists
     *
     * @param string $name
     * @return boolean
     */
    public function hasProvider(string $name): bool
    {
        $this->load();
        $provider = $this->serviceProviders[$name] ?? null;

        return !empty($provider);
    } 

    /**
     * Get service instance
     *
     * @param string $name
     * @return mixed|null
     */
    public function get(string $name)
    {             
        // check prividers
        $provider = $this->getProvider($name);
        if (empty($provider) == true) {
            return null;
        }

        // check container
        if ($this->container->has($name) == false) {
            // add in container
            $this->container[$name] = function() use($provider) {
                return new $provider();
            };
        }

        return $this->container->get($name);
    }

    /**
     * Return true if service exists in container
     *
     * @param string $name
     * @return boolean
     */
    public function has(string $name): bool
    {      
        return (bool)$this->container->has($name);
    }

    /**
     * Register service provider
     *
     * @param string $providerClass
     * @return boolean
     */
    public function register(string $providerClass): bool
    {
        if (\class_exists($providerClass) == false) {         
            return false;
        }
    
        $provider = new $providerClass();
     
        if (($provider instanceof ServiceInterface) == false) {
            throw new Exception('Service provider ' . $providerClass . ' not valid service class.');
            return false;
        }
        $serviceName = $provider->getServiceName();
        $this->load(true);

        $this->serviceProviders[$serviceName] = $providerClass;

        return $this->saveConfigFile($this->configFileName,$this->serviceProviders);       
    }

    /**
     * UnRegister service
     *
     * @param string $name  Name or provider class
     * @return boolean
     */
    public function unRegister(string $name): bool
    {
        if ($this->hasProvider($name) == false) {
            // find provider
            $name = \array_search($name,$this->getProviders());
            if ($name === false) {
                return true;
            }            
        }

        unset($this->serviceProviders[$name]);

        return $this->saveConfigFile($this->configFileName,$this->serviceProviders);     
    }
}
