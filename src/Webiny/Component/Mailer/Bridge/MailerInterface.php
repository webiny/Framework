<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Mailer\Bridge;

use Webiny\Component\Config\ConfigObject;
use Webiny\Component\Mailer\TransportInterface;

/**
 * The mailer interface defines the required methods that every mailer bridge library must implement.
 *
 * @package         Webiny\Component\Mailer\Bridge
 */
interface MailerInterface
{
    /**
     * Returns an instance of TransportInterface.
     *
     * @param ConfigObject $config The configuration of current mailer.
     *
     * @return TransportInterface
     */
    static function getTransport(ConfigObject $config);

    /**
     * Returns an instance of MessageInterface.
     *
     * @param ConfigObject $config The configuration of current mailer
     *
     * @return MessageInterface
     */
    static function getMessage(ConfigObject $config);

}