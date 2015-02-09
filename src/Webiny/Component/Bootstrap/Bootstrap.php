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
     * @param string $appPath Path to the application root.
     *
     * @throws BootstrapException
     * @throws \Exception
     */
    public function runApplication($appPath = '')
    {
        if ($appPath != '') {
            $rootPath = $appPath;
        } else {
            $rootPath = realpath(dirname(debug_backtrace()[0]['file']) . '/../');
        }

        // save the root path
        $this->_absolutePath = realpath($rootPath) . DIRECTORY_SEPARATOR;
        $this->initializeEnvironment($this->_absolutePath);
        $this->initializeRouter();
    }

    /**
     * Initializes the application environment.
     *
     * @param string $appPath Path to the application root.
     *
     * @throws BootstrapException
     * @throws \Exception
     */
    public function initializeEnvironment($appPath)
    {
        try {
            // initialize the environment and its configurations
            $this->_environment = Environment::getInstance();
            $this->_environment->initializeEnvironment($appPath);
        } catch (BootstrapException $e) {
            throw $e;
        }
    }

    /**
     * Initializes the router.
     *
     * @throws BootstrapException
     * @throws \Exception
     */
    public function initializeRouter()
    {
        try {
            // initialize router
            $this->_router = Router::getInstance();

            // if a route is matched, a dispatcher instance is returned, and the callback is issued
            $this->_router->initializeRouter()->issueCallback();
        } catch (BootstrapException $e) {
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