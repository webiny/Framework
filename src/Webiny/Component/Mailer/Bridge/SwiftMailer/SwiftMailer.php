<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Mailer\Bridge\SwiftMailer;

use Webiny\Component\Mailer\Bridge\MailerInterface;
use Webiny\Component\Config\ConfigObject;
use Webiny\Component\Mailer\MessageInterface;
use Webiny\Component\Mailer\TransportInterface;

/**
 * This class is a wrapper for loading Mailer components from the SwiftMailer bridge library.
 *
 * @package         Webiny\Component\Mailer\Bridge\SwiftMailer
 */
class SwiftMailer implements MailerInterface
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
        $message = new Message();

        $message->setCharset($config->get('CharacterSet', 'utf-8'));
        $message->setMaxLineLength($config->get('MaxLineLength', 78));

        if ($config->get('Priority', false)) {
            $message->setPriority($config->get('Priority', 3));
        }

        if ($config->get('Sender', false)) {
            $message->setSender($config->get('Sender.Email', 'me@localhost'), $config->get('Sender.Name', null));

            // Fix/Hack (wasn't in headers before)
            $message->setFrom($config->get('Sender.Email', 'me@localhost'), $config->get('Sender.Name', null));
        }

        return $message;
    }
}