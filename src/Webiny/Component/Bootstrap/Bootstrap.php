<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Bootstrap;

use Webiny\Component\StdLib\SingletonTrait;

/**
 * Bootstrap base class.
 *
 * @package         Webiny\Component\Bootstrap
 */
class Bootstrap
{
    use SingletonTrait;

    /**
     * @var string Application absolute root path.
     */
    private $_absolutePath;

    /**
     * @var Environment
     */
    private $_environment;

    /**
     * @var Router
     */
    private $_router;


    /**
     * Initializes the environment and the router.
     * Router takes the process from there.
     *
     * @throws BootstrapException
     * @throws \Exception
     */
    public function runApplication()
    {
        $rootPath = realpath(dirname(debug_backtrace()[0]['file']).'/../');

        // save the root path
        $this->_absolutePath = realpath($rootPath) . DIRECTORY_SEPARATOR;

        try{
            // initialize the environment and its configurations
            $this->_environment = Environment::getInstance();
            $this->_environment->initializeEnvironment($this->_absolutePath);

            // initialize router
            // router calls the dispatcher, which then issues the callback to the appropriate application class
            $this->_router = Router::getInstance();
            $this->_router->initializeRouter();
        }catch (BootstrapException $e){
            throw $e;
        }
    }

    /**
     * Returns current environment instance.
     *
     * @return Environment
     */
    public function getEnvironment()
    {
        return $this->_environment;
    }
}