<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Router;

/**
 * RouterTrait provides easier access to the Router component.
 *
 * @package         Webiny\Component\Router
 */

trait RouterTrait
{
    /**
     * Get the Router instance.
     * The returned Router instance uses routes defined inside your config as the default RouteCollection.
     *
     * @return Router
     */
    function router()
    {
        return Router::getInstance();
    }

}