<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Router\Loader;

use Webiny\Component\Config\ConfigObject;
use Webiny\Component\Router\Route\Route;
use Webiny\Component\Router\Route\RouteCollection;
use Webiny\Component\StdLib\StdLibTrait;

/**
 * ConfigLoader parses the given config file, extracts the routes and builds a RouteCollection object.
 *
 * @package         Webiny\Component\Router\Loader
 */
class ConfigLoader
{
    use StdLibTrait;

    /**
     * @var \Webiny\Component\Config\ConfigObject
     */
    private $_config;

    /**
     * @var \Webiny\Component\Router\Route\RouteCollection
     */
    private $_routeCollection;


    /**
     * Base constructor.
     *
     * @param ConfigObject $config Instance of config object containing route definitions.
     */
    function __construct(ConfigObject $config)
    {
        $this->_config = $config;
        $this->_routeCollection = new RouteCollection();
    }

    /**
     * Builds and returns RouteCollection instance.
     *
     * @return RouteCollection
     */
    function getRouteCollection()
    {
        foreach ($this->_config as $name => &$routeConfig) {
            $this->_routeCollection->add($name, $this->processRoute($routeConfig));
        }

        unset($this->_config);

        return $this->_routeCollection;
    }

    /**
     * Builds a Route instance based on the given route config.
     *
     * @param ConfigObject $routeConfig A config object containing route parameters.
     *
     * @return Route
     */
    function processRoute(ConfigObject $routeConfig)
    {
        // base route
        $callback = $this->isString($routeConfig->Callback) ? $routeConfig->Callback : $routeConfig->Callback->toArray();
        $route = new Route($routeConfig->Path, $callback);

        // route options
        if (($options = $routeConfig->get('Options', false)) != false) {
            $route->setOptions($options->toArray());
        }

        // host
        if (($host = $routeConfig->get('Host', false)) != false) {
            $route->setHost($host);
        }

        // schemes
        if (($schemes = $routeConfig->get('Schemes', false)) != false) {
            $route->setSchemes($schemes);
        }

        // methods
        if (($methods = $routeConfig->get('Methods', false)) != false) {
            $route->setMethods($methods->toArray());
        }

        return $route;
    }
}