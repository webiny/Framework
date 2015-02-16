<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\OAuth2\Server;

use Webiny\Component\OAuth2\OAuth2User;
use Webiny\Component\OAuth2\OAuth2Exception;
use Webiny\Component\OAuth2\ServerAbstract;
use Webiny\Component\StdLib\StdLibTrait;

/**
 * Google OAuth2 API wrapper.
 *
 * @package         Webiny\Component\OAuth2\Server
 */
class Google extends ServerAbstract
{

    use StdLibTrait;

    /**
     * Google API authorize url
     */
    const API_AUTH_URL = 'https://accounts.google.com/o/oauth2/auth
											?response_type=code
											&client_id={CLIENT_ID}
											&redirect_uri={REDIRECT_URI}
											&scope={SCOPE}
											&state={STATE}';

    /**
     * Google API access token url.
     */
    const API_ACCESS_TOKEN = 'https://accounts.google.com/o/oauth2/token';

    /**
     * Google API - get user info.
     */
    const API_USER_INFO = 'https://www.googleapis.com/oauth2/v1/userinfo';


    /**
     * Returns the path to OAuth2 authorize page.
     *
     * @return string Url to OAuth2 authorize page.
     */
    function getAuthorizeUrl()
    {
        return self::API_AUTH_URL;
    }

    /**
     * Returns the path to the page where we request the access token.
     *
     * @return string Url to access token page.
     */
    function getAccessTokenUrl()
    {
        return self::API_ACCESS_TOKEN;
    }

    /**
     * Returns the server name.
     *
     * @return string
     */
    public function getServerName()
    {
        return 'Google';
    }

    /**
     * Returns an array [url, params].
     * 'url' - holds the destination url for accessing user details on the OAuth2 server.
     * 'params' - an optional array of additional parameters that would be sent together with the request.
     *
     * @return array
     */
    protected function _getUserDetailsTargetData()
    {
        return [
            'url'    => self::API_USER_INFO,
            'params' => []
        ];
    }

    /**
     * This method is called automatically when the OAuth2 server returns a response containing user details.
     * The method should process the response an return and instance of OAuth2User.
     *
     * @param array $result OAuth2 server response.
     *
     * @return OAuth2User
     * @throws OAuth2Exception
     */
    protected function _processUserDetails($result)
    {
        $result = self::arr($result['result']);
        if ($result->keyExists('error')) {
            throw new OAuth2Exception($result->key('error'));
        }

        $user = new OAuth2User($result->key('given_name'), $result->key('email', '', true));
        $user->setProfileId($result->key('id', '', true));
        $user->setFirstName($result->key('given_name', '', true));
        $user->setLastName($result->key('family_name', '', true));
        $user->setProfileUrl($result->key('link', '', true));
        $user->setAvatarUrl($result->key('picture', '', true));
        $user->setGender($result->key('gender', '', true));
        $user->setServiceName('google');

        return $user;
    }
}