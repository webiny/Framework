<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\TwitterOAuth\Bridge\TwitterOAuth;

use Webiny\Component\TwitterOAuth\Bridge\TwitterOAuthInterface;

/**
 * Bridge for TwitterOAuth library by Abraham Williams (https://github.com/abraham/twitteroauth)
 *
 * @package         Webiny\Component\TwitterOAuth\Bridge\TwitterOAuth
 */
class TwitterOAuth implements TwitterOAuthInterface
{

    /**
     * @var null|\TwitterOAuth
     */
    private $_instance = null;

    /**
     * OAuth2 Client ID.
     *
     * @var string
     */
    protected $_clientId = '';

    /**
     * Client secret for defined Client ID.
     *
     * @var string
     */
    protected $_clientSecret = '';


    /**
     * Base constructor.
     *
     * @param string $clientId     Client id.
     * @param string $clientSecret Client secret.
     * @param string $redirectUri  Target url where to redirect after authentication.
     */
    public function __construct($clientId, $clientSecret, $redirectUri)
    {
        $this->_clientId = $clientId;
        $this->_clientSecret = $clientSecret;
        $this->_redirectUri = $redirectUri;

        $this->_instance = new \Abraham\TwitterOAuth\TwitterOAuth($clientId, $clientSecret);
    }

    /**
     * Get client id.
     *
     * @return string Client id.
     */
    public function getClientId()
    {
        return $this->_clientId;
    }

    /**
     * Get client secret.
     *
     * @return string Client secret.
     */
    public function getClientSecret()
    {
        return $this->_clientSecret;
    }

    /**
     * Get redirect uri.
     *
     * @return string Redirect uri.
     */
    public function getRedirectUri()
    {
        //NOTE: this path is automatically translated to full path on TwitterOAuthLoader class
        return $this->_redirectUri;
    }

    /**
     * Get the request token.
     *
     * @return string Request token.
     */
    public function getRequestToken()
    {
        return $this->_instance->getRequestToken($this->getRedirectUri());
    }

    /**
     * Get the response code in http format.
     * Example return: 200
     *
     * @return int Response code.
     */
    public function getResponseCode()
    {
        return $this->_instance->http_code;
    }

    /**
     * Get the \TwitterOAuth instance.
     *
     * @return null|\TwitterOAuth
     */
    public function getDriverInstance()
    {
        return $this->_instance;
    }

    /**
     * Set \TwitterOAuth instance.
     *
     * @param \TwitterOAuth $instance
     */
    public function setDriverInstance(\Abraham\TwitterOAuth\TwitterOAuth $instance)
    {
        $this->_instance = $instance;
    }

    /**
     * Get the authorize url.
     *
     * @param string|array $requestToken Request token returned by Twitter OAuth server.
     *
     * @return string
     */
    public function getAuthorizeUrl($requestToken)
    {
        return $this->_instance->getAuthorizeURL($requestToken);
    }

    /**
     * Once we have token, we can run the authorization which than give us the option to request the access token.
     *
     * @param string $token
     * @param string $tokenSecret
     *
     * @return void
     */
    public function authorize($token, $tokenSecret)
    {
        $this->_instance = new \Abraham\TwitterOAuth\TwitterOAuth($this->getClientId(), $this->getClientSecret(),
                                                                  $token, $tokenSecret
        );
    }

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
    public function getAccessToken($verifier)
    {
        return $this->_instance->getAccessToken($verifier);
    }

    /**
     * Sets the access token.
     *
     * @param array $accessToken Array[oauth_token, oauth_token_secret]
     *
     * @throws TwitterOAuthException
     * @return void
     */
    public function setAccessToken(array $accessToken)
    {
        // check keys
        if (!isset($accessToken['oauth_token']) || !isset($accessToken['oauth_token_secret'])) {

            throw new TwitterOAuthException('All required keys must be present inside the token array. The requested keys are [oauth_token, oauth_token_secret].'
            );

        }

        $this->_instance = new \Abraham\TwitterOAuth\TwitterOAuth($this->getClientId(), $this->getClientSecret(),
                                                                  $accessToken['oauth_token'],
                                                                  $accessToken['oauth_token_secret']
        );

    }

    /**
     * GET wrapper for oAuthRequest.
     *
     * @param string $url    Api url.
     * @param array  $params Additional parameters.
     *
     * @return string|array Api response (if json) it will be returned as array.
     */
    public function get($url, array $params = [])
    {
        return $this->_instance->get($url, $params);
    }

    /**
     * Make a POST request to Twitter API.
     *
     * @param string $url    Api url.
     * @param array  $params Additional parameters.
     *
     * @return string|array Api response (if json) it will be returned as array.
     */
    public function post($url, array $params = [])
    {
        return $this->_instance->post($url, $params);
    }

    /**
     * Make a DELETE request to Twitter API.
     *
     * @param string $url    Api url.
     * @param array  $params Additional parameters.
     *
     * @return string|array Api response (if json) it will be returned as array.
     */
    public function delete($url, array $params = [])
    {
        return $this->_instance->delete($url, $params);
    }
}