<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Mailer\Bridge;

use Webiny\Component\Config\ConfigObject;
use Webiny\Component\Mailer\Mailer;
use Webiny\Component\Mailer\MailerException;
use Webiny\Component\Mailer\TransportInterface;
use Webiny\Component\StdLib\FactoryLoaderTrait;
use Webiny\Component\StdLib\StdLibTrait;
use WebinyPlatform\Apps\Core\Components\DevTools\Lib\Config;

/**
 * Provides static functions for getting the message instance and transport instance.
 *
 * @package Webiny\Component\Mailer\Bridge
 */
class Loader
{
    use FactoryLoaderTrait, StdLibTrait;

    /**
     * @var string Default Mailer bridge.
     */
    private static $library = '\Webiny\Component\Mailer\Bridge\SwiftMailer\SwiftMailer';

    /**
     * Returns an instance of MessageInterface based on current bridge.
     *
     * @param              $mailer
     *
     * @param ConfigObject $config
     *
     * @return \Webiny\Component\Mailer\MessageInterface
     * @throws MailerException
     * @throws \Webiny\Component\StdLib\Exception\Exception
     */
    public static function getMessage($mailer, ConfigObject $config = null)
    {
        // Do it this way to avoid merging into the original mailer config
        $mailerConfig = Mailer::getConfig()->get($mailer)->toArray();
        $mailerConfig = new ConfigObject($mailerConfig);
        if ($config) {
            $mailerConfig->mergeWith($config);
        }
        $lib = self::getLibrary($mailer);

        /** @var MailerInterface $libInstance */
        $libInstance = self::factory($lib, '\Webiny\Component\Mailer\Bridge\MailerInterface');

        $instance = $libInstance::getMessage($mailerConfig);
        if (!self::isInstanceOf($instance, '\Webiny\Component\Mailer\Bridge\MessageInterface')) {
            throw new MailerException(MailerException::MESSAGE_INTERFACE);
        }

        return $instance;
    }

    /**
     * Returns an instance of TransportInterface based on current bridge.
     *
     * @param string $mailer
     *
     * @return TransportInterface
     * @throws MailerException
     * @throws \Webiny\Component\StdLib\Exception\Exception
     */
    public static function getTransport($mailer)
    {
        $config = Mailer::getConfig()->get($mailer);
        if (!$config) {
            throw new MailerException(MailerException::INVALID_CONFIGURATION, [$mailer]);
        }

        $lib = self::getLibrary($mailer);

        /** @var MailerInterface $libInstance */
        $libInstance = self::factory($lib, '\Webiny\Component\Mailer\Bridge\MailerInterface');

        $instance = $libInstance::getTransport($config);
        if (!self::isInstanceOf($instance, '\Webiny\Component\Mailer\Bridge\TransportInterface')) {
            throw new MailerException(MailerException::TRANSPORT_INTERFACE);
        }

        return $instance;
    }

    /**
     * Change the default library used for the driver.
     *
     * @param string $pathToClass Path to the new driver class. Must be an instance of \Webiny\Component\Mailer\Bridge\MailerInterface
     */
    public static function setLibrary($pathToClass)
    {
        self::$library = $pathToClass;
    }

    /**
     * Get the name of bridge library which will be used as the driver.
     *
     * @param string $mailer
     *
     * @return string
     */
    protected static function getLibrary($mailer)
    {
        return Mailer::getConfig()->get('Bridge.' . $mailer, self::$library);
    }
}