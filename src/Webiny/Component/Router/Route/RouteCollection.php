<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Router\Route;

use Webiny\Component\StdLib\StdLibTrait;

/**
 * RouteCollection contains a set Route instances.
 * Note that if you store two routes with the same name, the first route will be overwritten.
 *
 * @package         Webiny\Component\Router\Route
 */
class RouteCollection
{
    use StdLibTrait;

    /**
     * @var \Webiny\Component\StdLib\StdObject\ArrayObject\ArrayObject
     */
    private $_routes;


    /**
     * Base constructor.
     */
    function __construct()
    {
        $this->_routes = $this->arr([]);
    }

    /**
     * Adds a Route to the end of current collection.
     *
     * @param string $name  Route name.
     * @param Route  $route Instance of Route.
     */
    function add($name, Route $route)
    {
        $this->_routes[$name] = $route;
    }

    /**
     * Adds a Route to the beginning of current collection.
     *
     * @param string $name  Route name.
     * @param Route  $route Instance of Route.
     */
    function prepend($name, Route $route)
    {
        $this->_routes->prepend($name, $route);
    }

    /**
     * Removes the route under the given name.
     *
     * @param string $name Route name.
     *
     * @return $this
     */
    function remove($name)
    {
        return $this->_routes->removeKey($name);
    }

    /**
     * Returns the number of routes within the current collection.
     *
     * @return int
     */
    function count()
    {
        return $this->_routes->count();
    }

    /**
     * Returns the route under the given name.
     *
     * @param string $name Name of the route.
     *
     * @return Route
     */
    function get($name)
    {
        return $this->_routes->key($name, null, true);
    }

    /**
     * Returns an array of all routes within the collection.
     *
     * @return array
     */
    function all()
    {
        return $this->_routes->val();
    }

    /**
     * Sets the host filter to all routes within the collection.
     *
     * @param string $host Host name. Example: www.webiny.com
     */
    function setHost($host)
    {
        /**
         * @var Route $route
         */
        foreach ($this->_routes as $route) {
            $route->setHost($host);
        }
    }

    /**
     * Sets the scheme filter to all routes within the collection.
     *
     * @param array|string $schemes Url scheme. Example: https
     */
    function setSchemes($schemes)
    {
        /**
         * @var Route $route
         */
        foreach ($this->_routes as $route) {
            $route->setSchemes($schemes);
        }
    }

    /**
     * Sets the method filter to all routes within the collection.
     *
     * @param array|string $methods Url method. Example: POST | GET
     */
    function setMethods($methods)
    {
        /**
         * @var Route $route
         */
        foreach ($this->_routes as $route) {
            $route->setMethods($methods);
        }
    }

    /**
     * Adds an option for all the routes within the collection.
     *
     * @param string $name       Name of the route parameter.
     * @param array  $attributes Parameter attributes.
     */
    function addOption($name, array $attributes)
    {
        /**
         * @var Route $route
         */
        foreach ($this->_routes as $route) {
            $route->addOption($name, $attributes);
        }
    }
}