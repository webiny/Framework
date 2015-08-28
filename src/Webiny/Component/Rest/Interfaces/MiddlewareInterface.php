<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Rest\Interfaces;

use Webiny\Component\Rest\Response\RequestBag;

/**
 * Implement middleware interface if you want to handle Rest method execution for yourself.
 * Everything is still handled by the component, except a call to the actual service, so this gives you an extra layer
 * of control over how a service method will be executed.
 *
 * Whatever you return from getResult method will be passed directly to Rest component as a service call result.
 *
 * @package Webiny\Component\Rest\Interfaces
 */

interface MiddlewareInterface
{
    /**
     * This method will be called if Middleware key is defined in Rest config.
     *
     * @param RequestBag $requestBag Request data from Rest component
     *
     * @return mixed
     */
    public function getResult(RequestBag $requestBag);
}