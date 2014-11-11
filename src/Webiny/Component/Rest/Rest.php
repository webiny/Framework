<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Rest;

use Webiny\Component\Rest\Compiler\Cache;
use Webiny\Component\Rest\Compiler\Compiler;
use Webiny\Component\Rest\Parser\Parser;
use Webiny\Component\Rest\Response\Router;
use Webiny\Component\StdLib\ComponentTrait;

/**
 * Rest class provides the main methods for registering and building API requests.
 *
 * @package    Webiny\Component\Rest
 */
class Rest
{
    use ComponentTrait;

    /**
     * @var string Name of the api configuration.
     */
    private $_api;

    /**
     * @var string Environment, can either be 'development' or 'production'.
     */
    private $_environment = 'production';

    /**
     * @var mixed|\Webiny\Component\Config\ConfigObject Rest api specific configuration.
     */
    private $_config;

    /**
     * @var string Name of the class that has been registered.
     */
    private $_class;


    /**
     * Base constructor.
     *
     * @param string $api   Name of the api configuration.
     * @param string $class Name of the class that will been registered with the api.
     *
     * @throws RestException
     */
    public function __construct($api, $class)
    {
        $this->_config = $this->getConfig()->get($api, false);
        if (!$this->_config) {
            throw new RestException('Configuration for "' . $api . '" not found.');
        }

        $this->setEnvironment($this->_config->get('Environment', 'production'));

        $this->_api = $api;
        $this->_class = $class;

        $this->_registerClass();
    }

    /**
     * Set the component environment.
     * If it's development, the component will output additional information inside the debug header.
     *
     * @param string $env Can either be 'development' or 'production'
     *
     * @throws RestException
     */
    public function setEnvironment($env)
    {
        if ($env != 'production' && $env != 'development') {
            throw new RestException('Unknown environment "' . $env . '".');
        }

        $this->_environment = $env;
    }

    /**
     * Get the name of current environment.
     *
     * @return string
     */
    public function getEnvironment()
    {
        return $this->_environment;
    }

    /**
     * Processes the current request and returns an instance of CallbackResult.
     *
     * @return bool|Response\CallbackResult
     * @throws RestException
     */
    public function processRequest()
    {
        try {
            $router = new Router($this->_api, $this->_class);

            return $router->processRequest();
        } catch (\Exception $e) {
            throw new RestException('Unable to process request for class "' . $this->_class . '". ' . $e->getMessage());
        }
    }

    /**
     * Returns true if current environment is 'development'.
     *
     * @return bool
     */
    private function _isDevelopment()
    {
        return ($this->_environment == 'development') ? true : false;
    }

    /**
     * Registers the class and creates a compile cache version of it.
     *
     * @throws RestException
     */
    private function _registerClass()
    {
        try {
            if (!Cache::isCacheValid($this->_api, $this->_class) || $this->_isDevelopment()) {
                try {
                    $this->_parseClass();
                } catch (\Exception $e) {
                    throw new RestException('Error registering class "' . $this->_class . '". ' . $e->getMessage());
                }
            }
        } catch (\Exception $e) {
            throw new RestException('Unable to register class "' . $this->_class . '". ' . $e->getMessage());
        }
    }

    /**
     * Calls the Parser to parse the class and
     * then Compiler to create a compiled cache file of the parsed class.
     */
    private function _parseClass()
    {
        $parser = new Parser();
        $parsedApi = $parser->parseApi($this->_class);

        // in development we always write cache
        $writer = new Compiler($this->_api);
        $writer->writeCacheFiles($parsedApi);
    }
}