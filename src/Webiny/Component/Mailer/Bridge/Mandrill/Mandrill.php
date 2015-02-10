<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Mailer\Bridge\Mandrill;

use Webiny\Component\Mailer\Bridge\MailerInterface;
use Webiny\Component\Config\ConfigObject;
use Webiny\Component\Mailer\MessageInterface;
use Webiny\Component\Mailer\TransportInterface;

/**
 * This class is a wrapper for loading Mailer components from the Mandrill bridge library.
 *
 * @package         Webiny\Component\Mailer\Bridge\Mandrill
 */
class Mandrill implements MailerInterface
{
    /**
     * Returns an instance of TransportInterface.
     *
     * @param ConfigObject $config The configuration of current mailer.
     *
     * @return TransportInterface
     */
    public static function getTransport(ConfigObject $config)
    {
        return new Transport($config);
    }

    /**
     * Returns an instance of MessageInterface.
     *
     * @param ConfigObject $config The configuration of current mailer
     *
     * @return MessageInterface
     */
    public static function getMessage(ConfigObject $config)
    {
        return new Message($config);
    }
}