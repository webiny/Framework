<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Rest\Interfaces;

/**
 * Implement access interface if you don't wish that Rest component automatically handles the
 * access control checks for you.
 *
 * @package Webiny\Component\Rest\Interfaces
 */

interface AccessInterface
{
    /**
     * This method will be call if the api class, or the method, requires a specific role to access.
     *
     * @rest.ignore
     *
     * @param string $role Name of the role specified in "rest.role" annotation.
     *
     * @return bool Boolean true should be returned if access is allowed, otherwise false should be returned.
     */
    public function hasAccess($role);
}