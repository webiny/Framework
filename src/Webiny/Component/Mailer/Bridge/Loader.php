<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Mailer\Bridge;

use Webiny\Component\Config\ConfigObject;
use Webiny\Component\Mailer\Mailer;
use Webiny\Component\StdLib\FactoryLoaderTrait;
use Webiny\Component\StdLib\StdLibTrait;

/**
 * Provides static functions for getting the message instance and transport instance.
 *
 * @package         Webiny\Component\Mailer\Bridge
 */
class Loader
{
    use FactoryLoaderTrait, StdLibTrait;

    /**
     * @var string Default Mailer bridge.
     */
    private static $_library = '\Webiny\Component\Mailer\Bridge\SwiftMailer\SwiftMailer';

    /**
     * Returns an instance of MessageInterface based on current bridge.
     *
     * @param ConfigObject $config
     *
     * @throws MailerException
     *
     * @return \Webiny\Component\Mailer\MessageInterface
     */
    public static function getMessage(ConfigObject $config)
    {
        $lib = self::_getLibrary();

        /** @var MailerInterface $libInstance */
        $libInstance = self::factory($lib, '\Webiny\Component\Mailer\Bridge\MailerInterface');

        $instance = $libInstance::getMessage($config);
        if (!self::isInstanceOf($instance, '\Webiny\Component\Mailer\Bridge\MessageInterface')) {
            throw new MailerException('The message library must implement "\Webiny\Component\Mailer\Bridge\MessageInterface".'
            );
        }

        return $instance;
    }

    /**
     * Returns an instance of TransportInterface based on current bridge.
     *
     * @param ConfigObject $config
     *
     * @throws MailerException
     * @return \Webiny\Component\Mailer\TransportInterface
     */
    public static function getTransport(ConfigObject $config)
    {
        $lib = self::_getLibrary();

        /** @var MailerInterface $libInstance */
        $libInstance = self::factory($lib, '\Webiny\Component\Mailer\Bridge\MailerInterface');

        $instance = $libInstance::getTransport($config);
        if (!self::isInstanceOf($instance, '\Webiny\Component\Mailer\Bridge\TransportInterface')) {
            throw new MailerException('The message library must implement "\Webiny\Component\Mailer\Bridge\TransportInterface".'
            );
        }

        return $instance;
    }

    /**
     * Get the name of bridge library which will be used as the driver.
     *
     * @return string
     */
    public static function _getLibrary()
    {
        return Mailer::getConfig()->get('Bridge', self::$_library);
    }

    /**
     * Change the default library used for the driver.
     *
     * @param string $pathToClass Path to the new driver class. Must be an instance of \Webiny\Component\Mailer\Bridge\MailerInterface
     */
    public static function setLibrary($pathToClass)
    {
        self::$_library = $pathToClass;
    }

}