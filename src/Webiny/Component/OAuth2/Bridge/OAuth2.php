<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\OAuth2\Bridge;

use Webiny\Component\StdLib\Exception\Exception;
use Webiny\Component\StdLib\StdLibTrait;

/**
 * OAuth2 bridge to external OAuth2 libraries.
 *
 * @package         Webiny\Component\OAuth2\Bridge
 */
class OAuth2
{
    use StdLibTrait;

    /**
     * Path to the default OAuth2 bridge library.
     *
     * @var string
     */
    private static $_library = '\Webiny\Component\OAuth2\Bridge\League\OAuth2';

    /**
     * Get the name of bridge library which will be used as the driver.
     *
     * @return string
     */
    static function _getLibrary()
    {
        return \Webiny\Component\OAuth2\OAuth2::getConfig()->get('Bridge', self::$_library);
    }

    /**
     * Change the default library used for the driver.
     *
     * @param string $pathToClass Path to the new bridge class.
     *                            The class must implement \Webiny\Component\OAuth2\Bridge\OAuth2Interface.
     */
    static function setLibrary($pathToClass)
    {
        self::$_library = $pathToClass;
    }

    /**
     * Create an instance of an OAuth2 driver.
     *
     * @param string $clientId     Client id.
     * @param string $clientSecret Client secret.
     * @param string $redirectUri  Target url where to redirect after authentication.
     * @param string $certificateFile
     *
     * @throws Exception
     * @return OAuth2Abstract
     */
    static function getInstance($clientId, $clientSecret, $redirectUri, $certificateFile = '')
    {
        $driver = static::_getLibrary();

        try {
            $instance = new $driver($clientId, $clientSecret, $redirectUri);
        } catch (\Exception $e) {
            throw new Exception('Unable to create an instance of ' . $driver);
        }

        if (!self::isInstanceOf($instance, '\Webiny\Component\OAuth2\Bridge\OAuth2Interface')) {
            throw new Exception(Exception::MSG_INVALID_ARG, [
                    'driver',
                    '\Webiny\Component\OAuth2\Bridge\OAuth2Interface'
                ]
            );
        }

        if ($certificateFile != '') {
            $instance->setCertificate($certificateFile);
        }

        return $instance;
    }
}