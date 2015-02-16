<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\TwitterOAuth\Bridge;

/**
 * TwitterOAuth interface must be implemented by all TwitterOAuth bridge drivers.
 *
 * @package         Webiny\Component\TwitterOAuth\Bridge
 */

interface TwitterOAuthInterface
{
    /**
     * Base constructor.
     *
     * @param string $clientId     Client id.
     * @param string $clientSecret Client secret.
     * @param string $redirectUri  Target url where to redirect after authentication.
     */
    public function __construct($clientId, $clientSecret, $redirectUri);

    /**
     * Get the request token (temporary credentials).
     *
     * @return array Request token [oauth_token, oauth_token_secret].
     */
    public function getRequestToken();

    /**
     * Get the authorize url.
     *
     * @param string|array $requestToken Request token returned by Twitter OAuth server.
     *
     * @return string
     */
    public function getAuthorizeUrl($requestToken);

    /**
     * Once we have token, we can run the authorization which than give us the option to request the access token.
     *
     * @param string $requestToken Request token returned by getRequestToken method.
     * @param string $requestTokenSecret Request token secret returned by getRequestToken method.
     * @param string $oauthToken OAuth token returned by Twitter OAuth server.
     * @param string $oauthTokenVerifier OAuth token verifier returned by Twitter OAuth server.
     *
     * @return string
     */
    public function requestAccessToken($requestToken, $requestTokenSecret, $oauthToken, $oauthTokenVerifier);

    /**
     * Sets the access token.
     * Should throw an exception if it's unable to set the access token.
     *
     * @param array $accessToken Array[oauth_token, oauth_token_secret]
     *
     * @return void
     */
    public function setAccessToken(array $accessToken);

    /**
     * Returns the current access token.
     *
     * @return Array|bool False is returned if the access token is not set.
     */
    public function getAccessToken();

    /**
     * Returns an instance of TwitterOAuthUser.
     *
     * @return TwitterOAuthUser
     */
    public function getUserDetails();

    /**
     * Make a GET request to Twitter API.
     *
     * @param string $url    Api url.
     * @param array $headers Request headers.
     * @param array  $params Additional parameters.
     *
     * @return string|array Api response (if json) it will be returned as array.
     */
    public function get($url, array $headers = [], array $params = []);

    /**
     * Make a POST request to Twitter API.
     *
     * @param string $url    Api url.
     * @param array $postBody Post body.
     * @param array $headers Request headers.
     * @param array  $params Additional parameters.
     *
     * @return string|array Api response (if json) it will be returned as array.
     */
    public function post($url, array $postBody, array $headers = [], array $params = []);

    /**
     * Make a DELETE request to Twitter API.
     *
     * @param string $url    Api url.
     * @param array $headers Request headers.
     * @param array  $params Additional parameters.
     *
     * @return string|array Api response (if json) it will be returned as array.
     */
    public function delete($url, array $headers = [], array $params = []);
}
