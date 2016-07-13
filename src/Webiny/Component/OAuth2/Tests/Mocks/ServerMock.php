<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\OAuth2\Tests\Mocks;

use Webiny\Component\OAuth2\OAuth2User;
use Webiny\Component\OAuth2\AbstractServer;

/**
 * A mocked OAuth2 bridge.
 *
 * @package         Webiny\Component\OAuth2\Tests\Mocks
 */
class ServerMock extends AbstractServer
{

    /**
     * Returns the path to OAuth2 authorize page.
     *
     * @return string Url to OAuth2 authorize page.
     */
    public function getAuthorizeUrl()
    {
        return 'http://www.webiny.com/oa2/?client_id={CLIENT_ID}
											&redirect_uri={REDIRECT_URI}
											&scope={SCOPE}
											&state={STATE}';
    }

    /**
     * Returns the path to the page where we request the access token.
     *
     * @return string Url to access token page.
     */
    public function getAccessTokenUrl()
    {
        // TODO: Implement getAccessTokenUrl() method.
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
        // TODO: Implement _getUserDetailsTargetData() method.
    }

    /**
     * This method is called automatically when the OAuth2 server returns a response containing user details.
     * The method should process the response an return and instance of OAuth2User.
     *
     * @param array $result OAuth2 server response.
     *
     * @return OAuth2User
     * @throws \OAuth2\Exception
     */
    protected function processUserDetails($result)
    {
        // TODO: Implement _processUserDetails() method.
    }

    /**
     * This method is called when user is redirected to the redirect_uri from the authorization step.
     * Here you should process the response from OAuth2 server and extract the access token if possible.
     * If you cannot get the access token, throw an exception.
     *
     * @param array $response Response from the OAuth2 server.
     *
     * @return string Access token.
     */
    public function processAuthResponse($response)
    {
        return 'access_token';
    }

    /**
     * Returns the server name.
     *
     * @return string
     */
    public function getServerName()
    {
        return 'ServerMock';
    }
}