<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\TwitterOAuth\Bridge;

use Webiny\Component\StdLib\StdLibTrait;

/**
 * TwitterOAuth bridge class
 *
 * @package         Webiny\Component\TwitterOauth\Bridge
 */
class TwitterOAuth
{
    use StdLibTrait;

    /**
     * Path to the default TwitterOAuth bridge library.
     *
     * @var string
     */
    private static $_library = '\Webiny\Component\TwitterOAuth\Bridge\League\TwitterOAuth';

    /**
     * Get the name of bridge library which will be used as the driver.
     *
     * @return string
     */
    private static function _getLibrary()
    {
        return \Webiny\Component\TwitterOAuth\TwitterOAuth::getConfig()->get('Bridge', self::$_library);
    }

    /**
     * Change the default library used for the driver.
     *
     * @param string $pathToClass Path to the new bridge class.
     *                            The class must implement \Webiny\Component\TwitterOAuth\Bridge\TwitterOAuthInterface.
     */
    public static function setLibrary($pathToClass)
    {
        self::$_library = $pathToClass;
    }

    /**
     * Create an instance of an TwitterOAuth driver.
     *
     * @param string $clientId     Client id.
     * @param string $clientSecret Client secret.
     * @param string $redirectUri  Target url where to redirect after authentication.
     *
     * @throws TwitterOAuthException
     * @return TwitterOAuthInterface
     */
    public static function getInstance($clientId, $clientSecret, $redirectUri)
    {
        $driver = static::_getLibrary();

        try {
            $instance = new $driver($clientId, $clientSecret, $redirectUri);
        } catch (\Exception $e) {
            throw new TwitterOAuthException('Unable to create an instance of ' . $driver);
        }

        if (!self::isInstanceOf($instance, '\Webiny\Component\TwitterOAuth\Bridge\TwitterOAuthInterface')) {
            throw new TwitterOAuthException(TwitterOAuthException::MSG_INVALID_ARG, [
                    'driver',
                    '\Webiny\Component\OAuth2\TwitterOAuth\TwitterOAuthInterface'
                ]
            );
        }

        return $instance;
    }
}