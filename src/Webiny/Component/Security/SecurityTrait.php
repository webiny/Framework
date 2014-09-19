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
     * Returns the current security instance.
     *
     * @return Security
     */
    protected static function security()
    {
        return Security::getInstance();
    }
}