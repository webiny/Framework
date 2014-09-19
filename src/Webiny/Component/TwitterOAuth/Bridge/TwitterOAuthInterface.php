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
     * Get the request token.
     *
     * @return string Request token.
     */
    public function getRequestToken();

    /**
     * Get the response code in http format.
     * Example return: 200
     *
     * @return int Response code.
     */
    public function getResponseCode();

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
     * @param string $token
     * @param string $tokenSecret
     *
     * @return void
     */
    public function authorize($token, $tokenSecret);

    /**
     * Get the access token.
     *
     * @param string $verifier Token verifier.
     *
     * @return array ["oauth_token" => "the-access-token",
     *                "oauth_token_secret" => "the-access-secret",
     *                "user_id" => "5555",
     *                "screen_name" => "WebinyPlatform"]
     */
    public function getAccessToken($verifier);

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
     * Make a GET request to Twitter API.
     *
     * @param string $url    Api url.
     * @param array  $params Additional parameters.
     *
     * @return string|array Api response (if json) it will be returned as array.
     */
    public function get($url, array $params = []);

    /**
     * Make a POST request to Twitter API.
     *
     * @param string $url    Api url.
     * @param array  $params Additional parameters.
     *
     * @return string|array Api response (if json) it will be returned as array.
     */
    public function post($url, array $params = []);

    /**
     * Make a DELETE request to Twitter API.
     *
     * @param string $url    Api url.
     * @param array  $params Additional parameters.
     *
     * @return string|array Api response (if json) it will be returned as array.
     */
    public function delete($url, array $params = []);
}
