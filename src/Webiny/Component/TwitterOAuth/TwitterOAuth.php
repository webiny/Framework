<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\TwitterOAuth;

use Webiny\Component\StdLib\ComponentTrait;
use Webiny\Component\TwitterOAuth\Bridge\TwitterOAuthInterface;

/**
 * This component provides TwitterOAuth authorization.
 *
 * @package         Webiny\Component\TwitterOauth
 */
class TwitterOAuth
{
    use ComponentTrait;

    /**
     * @var Bridge\TwitterOAuthInterface
     */
    private $instance;

    /**
     * Base constructor.
     * Use the TwitterOAuthLoader::getInstance to get a TwitterOAuth instance.
     *
     * @param Bridge\TwitterOAuthInterface $instance
     */
    public function __construct(TwitterOAuthInterface $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Get the request token.
     *
     * @return string Request token.
     */
    public function getRequestToken()
    {
        return $this->instance->getRequestToken();
    }

    /**
     * Get the response code in http format.
     * Example return: 200
     *
     * @return int Response code.
     */
    public function getResponseCode()
    {
        return $this->instance->getResponseCode();
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
        return $this->instance->getAuthorizeUrl($requestToken);
    }

    /**
     * Once we have token, we can run the authorization which than give us the option to request the access token.
     *
     * @param string $requestToken       Request token returned by getRequestToken method.
     * @param string $requestTokenSecret Request token secret returned by getRequestToken method.
     * @param string $oauthToken         OAuth token returned by Twitter OAuth server.
     * @param string $oauthTokenVerifier OAuth token verifier returned by Twitter OAuth server.
     *
     * @return string
     */
    public function requestAccessToken($requestToken, $requestTokenSecret, $oauthToken, $oauthTokenVerifier)
    {
        return $this->instance->requestAccessToken($requestToken, $requestTokenSecret, $oauthToken, $oauthTokenVerifier
        );
    }

    /**
     * Sets the access token.
     * Should throw an exception if it's unable to set the access token.
     *
     * @param array $accessToken Array[oauth_token, oauth_token_secret]
     *
     * @return void
     */
    public function setAccessToken(array $accessToken)
    {
        $this->instance->setAccessToken($accessToken);
    }

    /**
     * Returns the current access token.
     *
     * @return Array|bool False is returned if the access token is not set.
     */
    public function getAccessToken()
    {
        return $this->instance->getAccessToken();
    }

    /**
     * Gets the user details for current authenticated user.
     *
     * @return TwitterOAuthUser
     * @throws TwitterOAuthException
     */
    public function getUserDetails()
    {
        try {
            return $this->instance->getUserDetails();
        } catch (\Exception $e) {
            throw new TwitterOAuthException($e->getMessage());
        }
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
        return $this->instance->get($url, $params);
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
        return $this->instance->post($url, $params);
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
        return $this->instance->delete($url, $params);
    }
}