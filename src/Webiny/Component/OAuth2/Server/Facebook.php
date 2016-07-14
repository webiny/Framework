<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\OAuth2\Server;

use Webiny\Component\OAuth2\OAuth2Exception;
use Webiny\Component\OAuth2\OAuth2User;
use Webiny\Component\OAuth2\AbstractServer;
use Webiny\Component\StdLib\StdLibTrait;

/**
 * Facebook OAuth2 wrapper for the Graph API.
 *
 * @package         Webiny\Component\OAuth2\Server
 */
class Facebook extends AbstractServer
{
    use StdLibTrait;

    /**
     * @link https://developers.facebook.com/docs/reference/api/user/
     */
    const API_ME = 'https://graph.facebook.com/me';

    /**
     * Facebook Graph API authorize url
     */
    const API_AUTH_URL = 'https://graph.facebook.com/oauth/authorize
											?response_type=code
											&client_id={CLIENT_ID}
											&redirect_uri={REDIRECT_URI}
											&scope={SCOPE}
											&state={STATE}';

    /**
     * Facebook Graph API access token url.
     */
    const API_ACCESS_TOKEN = 'https://graph.facebook.com/oauth/access_token';


    /**
     * Returns the path to OAuth2 authorize page.
     *
     * @return string Url to OAuth2 authorize page.
     */
    public function getAuthorizeUrl()
    {
        return self::API_AUTH_URL;
    }

    /**
     * Returns the path to the page where we request the access token.
     *
     * @return string Url to access token page.
     */
    public function getAccessTokenUrl()
    {
        return self::API_ACCESS_TOKEN;
    }

    /**
     * This method is called when user is redirected to the redirect_uri from the authorization step.
     * Here you should process the response from OAuth2 server and extract the access token if possible.
     * If you cannot get the access token, throw an exception.
     *
     * @param array $response Response from the OAuth2 server.
     *
     * @throws \Webiny\Component\OAuth2\OAuth2Exception
     * @return string Access token.
     */
    public function processAuthResponse($response)
    {
        if (!$this->isArray($response)) {
            throw new OAuth2Exception('Invalid response while trying to get the access token.');
        }

        if (isset($response['result']['error'])) {
            throw new OAuth2Exception($this->jsonEncode($response['result']['error']['message']));
        }

        parse_str($response['result'], $info);

        return $info['access_token'];
    }


    /**
     * Returns the server name.
     *
     * @return string
     */
    public function getServerName()
    {
        return 'Facebook';
    }

    /**
     * Returns an array [url, params].
     * 'url' - holds the destination url for accessing user details on the OAuth2 server.
     * 'params' - an optional array of additional parameters that would be sent together with the request.
     *
     * @return array
     */
    protected function getUserDetailsTargetData()
    {
        return [
            'url'    => self::API_ME,
            'params' => [
                'fields' => 'id,email,first_name,last_name,gender,link'
            ]
        ];
    }

    /**
     * This method is called automatically when the OAuth2 server returns a response containing user details.
     * The method should process the response an return and instance of OAuth2User.
     *
     * @param array $result OAuth2 server response.
     *
     * @return OAuth2User
     * @throws \Webiny\Component\OAuth2\OAuth2Exception
     */
    public function processUserDetails($result)
    {
        $result = $this->arr($result['result']);
        if ($result->keyExists('error')) {
            throw new OAuth2Exception($result->keyNested('error.message'));
        }

        $user = new OAuth2User($result->key('username', '', true), $result->key('email', '', true));
        $user->setProfileId($result->key('id', '', true));
        $user->setFirstName($result->key('first_name', '', true));
        $user->setLastName($result->key('last_name', '', true));
        $user->setProfileUrl($result->key('link', '', true));
        $user->setAvatarUrl('http://graph.facebook.com/' . $user->profileId . '/picture?width=160&height=160');
        $user->setLastUpdateTime(strtotime($result->key('updated_time', '', true)));
        $user->setGender($result->key('gender', '', true));
        $user->setServiceName('facebook');

        return $user;
    }
}