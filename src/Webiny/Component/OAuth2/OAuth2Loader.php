<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\OAuth2;

use Webiny\Component\Http\HttpTrait;
use Webiny\Component\StdLib\StdLibTrait;

/**
 * OAuth2 loader.
 * Use this class to get an instance of OAuth2 component.
 *
 * @package         Webiny\Component\OAuth2
 */
class OAuth2Loader
{
    use StdLibTrait, HttpTrait;

    private static $instances = [];

    /**
     * Returns an instance to OAuth2 server based on the current configuration.
     *
     * @param string $key Unique identifier for the OAuth2 server that you wish to get.
     *
     * @return array|OAuth2
     * @throws OAuth2Exception
     */
    public static function getInstance($key)
    {
        if (isset(self::$instances[$key])) {
            return self::$instances;
        }

        $oauth2Config = OAuth2::getConfig()->get($key, false);

        if (!$oauth2Config) {
            throw new OAuth2Exception('Unable to read "OAuth2.' . $key . '" configuration.');
        }

        if (self::str($oauth2Config->RedirectUri)->startsWith('http')) {
            $redirectUri = $oauth2Config->RedirectUri;
        } else {
            $redirectUri = self::httpRequest()
                               ->getCurrentUrl(true)
                               ->setPath($oauth2Config->RedirectUri)
                               ->setQuery('')
                               ->val();
        }

        $instance = Bridge\OAuth2::getInstance($oauth2Config->ClientId, $oauth2Config->ClientSecret, $redirectUri);

        $server = $oauth2Config->get('Server', false);
        if (!$server) {
            throw new OAuth2Exception('Server missing for "OAuth2.' . $key . '" configuration.');
        }

        $instance->setOAuth2Server($server);
        $instance->setScope($oauth2Config->get('Scope', ''));

        return new OAuth2($instance);
    }

}