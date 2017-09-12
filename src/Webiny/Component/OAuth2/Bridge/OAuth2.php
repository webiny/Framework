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
    private static $library = League\OAuth2::class;

    /**
     * Get the name of bridge library which will be used as the driver.
     *
     * @return string
     */
    static function getLibrary()
    {
        return \Webiny\Component\OAuth2\OAuth2::getConfig()->get('Bridge', self::$library);
    }

    /**
     * Change the default library used for the driver.
     *
     * @param string $pathToClass Path to the new bridge class.
     *                            The class must implement \Webiny\Component\OAuth2\Bridge\OAuth2Interface.
     */
    static function setLibrary($pathToClass)
    {
        self::$library = $pathToClass;
    }

    /**
     * Create an instance of an OAuth2 driver.
     *
     * @param string $clientId Client id.
     * @param string $clientSecret Client secret.
     * @param string $redirectUri Target url where to redirect after authentication.
     * @param string $certificateFile
     *
     * @throws Exception
     * @return AbstractOAuth2
     */
    static function getInstance($clientId, $clientSecret, $redirectUri, $certificateFile = '')
    {
        $driver = static::getLibrary();

        try {
            $instance = new $driver($clientId, $clientSecret, $redirectUri);
        } catch (\Exception $e) {
            throw new Exception('Unable to create an instance of ' . $driver);
        }

        if (!self::isInstanceOf($instance, OAuth2Interface::class)) {
            throw new Exception(Exception::MSG_INVALID_ARG, ['driver', OAuth2Interface::class]);
        }

        if ($certificateFile != '') {
            $instance->setCertificate($certificateFile);
        }

        return $instance;
    }
}