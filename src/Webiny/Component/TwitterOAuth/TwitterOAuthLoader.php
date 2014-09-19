<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\TwitterOAuth;

use Webiny\Component\Http\HttpTrait;
use Webiny\Component\StdLib\StdLibTrait;

/**
 * TwitterOAuth loader.
 * Use this class to get an instance of TwitterOAuth component.
 *
 * @package         Webiny\Component\TwitterOAuth
 */
class TwitterOAuthLoader
{

    use StdLibTrait, HttpTrait;

    private static $_instances = [];

    /**
     * Returns an instance to TwitterOAuth server based on the current configuration.
     *
     * @param string $key Unique identifier for the TwitterOAuth server that you wish to get.
     *
     * @return array|TwitterOAuth
     * @throws TwitterOAuthException
     */
    public static function getInstance($key)
    {
        if (isset(self::$_instances[$key])) {
            return self::$_instances;
        }

        $config = TwitterOAuth::getConfig()->get($key, false);

        if (!$config) {
            throw new TwitterOAuthException('Unable to read "TwitterOAuth.' . $key . '" configuration.');
        }

        if (strpos($config->RedirectUri, 'http://') !== false || strpos($config->RedirectUri, 'https://'
            ) !== false
        ) {
            $redirectUri = $config->RedirectUri;
        } else {
            $redirectUri = self::httpRequest()->getCurrentUrl(true)->setPath($config->RedirectUri)->setQuery('')->val();
        }

        $instance = \Webiny\Component\TwitterOAuth\Bridge\TwitterOAuth::getInstance($config->ClientId,
                                                                                    $config->ClientSecret, $redirectUri
        );

        return new TwitterOAuth($instance);
    }

}