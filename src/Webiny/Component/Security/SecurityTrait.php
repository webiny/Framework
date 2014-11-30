<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Security;

use Webiny\Component\Security\Authentication\Firewall;

/**
 * SecurityTrait provides you a simplified access to security context.
 *
 * @package         Webiny\Component\Security
 */

trait SecurityTrait
{

    /**
     * Returns the current security instance or firewall if firewall key is given
     *
     * @param null|string $firewall Firewall key
     *
     * @throws SecurityException
     * @return Security|Firewall
     */
    protected static function security($firewall = null)
    {
        if($firewall) {
            return Security::getInstance()->firewall($firewall);
        }

        return Security::getInstance();
    }
}