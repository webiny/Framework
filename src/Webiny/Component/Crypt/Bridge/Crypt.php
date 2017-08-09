<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Crypt\Bridge;

use Webiny\Component\StdLib\Exception\Exception;
use Webiny\Component\StdLib\StdLibTrait;

/**
 * Class that holds the bridge crypt instance
 *
 * @package         Webiny\Component\Crypt\Bridge
 */
class Crypt
{
    use StdLibTrait;

    /**
     * Path to the default bridge crypt library.
     *
     * @var string
     */
    private static $library = '\Webiny\Component\Crypt\Bridge\Openssl\Crypt';

    /**
     * Get the name of bridge library which will be used as the driver.
     *
     * @return string
     */
    static function getLibrary()
    {
        return \Webiny\Component\Crypt\Crypt::getConfig()->get('Bridge', self::$library);
    }

    /**
     * Change the default library used for the driver.
     *
     * @param string $pathToClass Path to the new bridge class.
     *                            The class must implement \Webiny\Component\Crypt\Bridge\CryptInterface.
     */
    static function setLibrary($pathToClass)
    {
        self::$library = $pathToClass;
    }

    /**
     * Create an instance of a crypt driver.
     *
     *
     * @throws \Webiny\Component\StdLib\Exception\Exception
     * @return CryptInterface
     */
    static function getInstance()
    {
        $driver = static::getLibrary();

        try {
            $instance = new $driver();
        } catch (\Exception $e) {
            throw new Exception('Unable to create an instance of ' . $driver);
        }

        if (!self::isInstanceOf($instance, '\Webiny\Component\Crypt\Bridge\CryptInterface')) {
            throw new Exception(Exception::MSG_INVALID_ARG, [
                    'driver',
                    '\Webiny\Component\Crypt\Bridge\CryptInterface'
                ]
            );
        }

        return $instance;
    }
}
 
