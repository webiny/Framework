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
    private $routes;


    /**
     * Base constructor.
     */
    public function __construct()
    {
        $this->routes = $this->arr([]);
    }

    /**
     * Adds a Route to the end of current collection.
     *
     * @param string $name  Route name.
     * @param Route  $route Instance of Route.
     */
    public function add($name, Route $route)
    {
        $this->routes[$name] = $route;
    }

    /**
     * Adds a Route to the beginning of current collection.
     *
     * @param string $name  Route name.
     * @param Route  $route Instance of Route.
     */
    public function prepend($name, Route $route)
    {
        $this->routes->prepend($name, $route);
    }

    /**
     * Removes the route under the given name.
     *
     * @param string $name Route name.
     *
     * @return $this
     */
    public function remove($name)
    {
        return $this->routes->removeKey($name);
    }

    /**
     * Returns the number of routes within the current collection.
     *
     * @return int
     */
    public function count()
    {
        return $this->routes->count();
    }

    /**
     * Returns the route under the given name.
     *
     * @param string $name Name of the route.
     *
     * @return Route
     */
    public function get($name)
    {
        return $this->routes->key($name, null, true);
    }

    /**
     * Returns an array of all routes within the collection.
     *
     * @return array
     */
    public function all()
    {
        return $this->routes->val();
    }

    /**
     * Sets the host filter to all routes within the collection.
     *
     * @param string $host Host name. Example: www.webiny.com
     */
    public function setHost($host)
    {
        /**
         * @var Route $route
         */
        foreach ($this->routes as $route) {
            $route->setHost($host);
        }
    }

    /**
     * Sets the scheme filter to all routes within the collection.
     *
     * @param array|string $schemes Url scheme. Example: https
     */
    public function setSchemes($schemes)
    {
        /**
         * @var Route $route
         */
        foreach ($this->routes as $route) {
            $route->setSchemes($schemes);
        }
    }

    /**
     * Sets the method filter to all routes within the collection.
     *
     * @param array|string $methods Url method. Example: POST | GET
     */
    public function setMethods($methods)
    {
        /**
         * @var Route $route
         */
        foreach ($this->routes as $route) {
            $route->setMethods($methods);
        }
    }

    /**
     * Adds an option for all the routes within the collection.
     *
     * @param string $name       Name of the route parameter.
     * @param array  $attributes Parameter attributes.
     */
    public function addOption($name, array $attributes)
    {
        /**
         * @var Route $route
         */
        foreach ($this->routes as $route) {
            $route->addOption($name, $attributes);
        }
    }
}