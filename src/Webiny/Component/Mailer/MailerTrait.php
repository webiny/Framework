<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Mailer;

/**
 * Mailer trait.
 *
 * @package Webiny\Component\Mailer
 */

trait MailerTrait
{

    private static $_mailerInstances;

    /**
     * Returns an instance of Mailer.
     *
     * @param string $key Key that identifies which mailer configuration to load.
     *
     * @return Mailer
     */
    protected static function mailer($key = 'Default')
    {
        if (isset(self::$_mailerInstances[$key])) {
            return self::$_mailerInstances[$key];
        } else {
            self::$_mailerInstances[$key] = new Mailer($key);

            return self::$_mailerInstances[$key];
        }
    }

}