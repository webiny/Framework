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
 * LinkedIn OAuth2 API wrapper.
 *
 * @package         Webiny\Component\OAuth2\Server
 */
class LinkedIn extends ServerAbstract
{
    use StdLibTrait;

    /**
     * LinkedIn API authorize url
     */
    const API_AUTH_URL = 'https://www.linkedin.com/uas/oauth2/authorization?response_type=code
                                           &client_id={CLIENT_ID}
                                           &scope={SCOPE}
                                           &state={STATE}
                                           &redirect_uri={REDIRECT_URI}';

    /**
     * LinkedIn API access token url.
     */
    const API_ACCESS_TOKEN = 'https://www.linkedin.com/uas/oauth2/accessToken';

    /**
     * LinkedIn API - user profile.
     */
    const API_PROFILE = 'https://api.linkedin.com/v1/people/~:(id,first-name,last-name,picture-url,email-address,public-profile-url)';


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
     * Returns an array [url, params].
     * 'url' - holds the destination url for accessing user details on the OAuth2 server.
     * 'params' - an optional array of additional parameters that would be sent together with the request.
     *
     * @return array
     */
    protected function _getUserDetailsTargetData()
    {
        return [
            'url'    => self::API_PROFILE,
            'params' => ['format' => 'json']
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
        if ($result->keyExists('status') && $result->key('status') != 200) {
            throw new OAuth2Exception($result->key('message'));
        }

        $user = new OAuth2User($result->key('firstName', '', true), $result->key('emailAddress', '', true));
        $user->setProfileId($result->key('id', '', true));
        $user->setFirstName($result->key('firstName', '', true));
        $user->setLastName($result->key('lastName', '', true));
        $user->setProfileUrl($result->key('publicProfileUrl', '', true));
        $user->setAvatarUrl($result->key('pictureUrl', '', true));
        $user->setServiceName('linkedin');

        return $user;
    }

    /**
     * This method is called when user is redirected to the redirect_uri from the authorization step.
     * Here you should process the response from OAuth2 server and extract the access token if possible.
     * If you cannot get the access token, throw an exception.
     *
     * @param array $response Response from the OAuth2 server.
     *
     * @throws OAuth2Exception
     * @return string Access token.
     */
    public function processAuthResponse($response)
    {
        if (!$this->isArray($response)) {
            throw new OAuth2Exception('Invalid response while trying to get the access token.');
        }

        if (isset($response['result']['status']) && $response['result']['status'] != 200) {
            throw new OAuth2Exception($response['result']['message']);
        }

        return $response['result']['access_token'];
    }
}