<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Security;

/**
 * SecurityTrait provides you a simplified access to security context.
 *
 * @package         Webiny\Component\Security
 */

trait SecurityTrait
{

    /**
     * Returns the current security instance or firewall for given firewall key
     *
     * @param null|string $firewall Firewall key
     *
     * @throws SecurityException
     * @return Security
     */
    protected static function security($firewall = null)
    {
        if($firewall) {
            return Security::getInstance()->firewall($firewall);
        }

        return Security::getInstance();
    }
}