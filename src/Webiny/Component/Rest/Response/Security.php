<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Rest\Response;

use Webiny\Component\Rest\RestException;
use Webiny\Component\Security\SecurityTrait;

/**
 * Security class verifies the defined access rules on the method with the defined firewall on the Rest config.
 *
 * @package         Webiny\Component\Rest\Response
 */
class Security
{
    use SecurityTrait;

    /**
     * Checks if current user has access to the current rest request.
     *
     * @param RequestBag $requestBag
     *
     * @return bool
     * @throws \Webiny\Component\Rest\RestException
     */
    public static function hasAccess(RequestBag $requestBag)
    {
        // first we check if method requires a special access level
        if (!$requestBag->getApiConfig()->get('Security', false)) {
            return true; // no special access level required
        }

        // get the required role
        if (isset($requestBag->getMethodData()['role'])) {
            $role = $requestBag->getMethodData()['role'];
        } else {
            $role = $requestBag->getApiConfig()->get('Security.Role', 'ROLE_ANONYMOUS');
        }

        // check if user has the access level required
        if ($requestBag->getClassData()['accessInterface']) {
            return $requestBag->getClassInstance()->hasAccess($role);
        } else {
            // get firewall name
            $firewallName = $requestBag->getApiConfig()->get('Security.Firewall', false);
            if (!$firewallName) {
                throw new RestException('When using Rest access rule, you must specify a Firewall in your configuration.
                Alternatively you can implement the AccessInterface and do the check on your own.'
                );
            }

            return self::security()->firewall($firewallName)->getUser()->hasRole($role);
        }
    }

}