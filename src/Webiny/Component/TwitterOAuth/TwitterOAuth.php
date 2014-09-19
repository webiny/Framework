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
    private $_instance;

    /**
     * Base constructor.
     * Use the TwitterOAuthLoader::getInstance to get a TwitterOAuth instance.
     *
     * @param Bridge\TwitterOAuthInterface $instance
     */
    public function __construct(TwitterOAuthInterface $instance)
    {
        $this->_instance = $instance;
    }

    /**
     * Get the request token.
     *
     * @return string Request token.
     */
    public function getRequestToken()
    {
        return $this->_instance->getRequestToken();
    }

    /**
     * Get the response code in http format.
     * Example return: 200
     *
     * @return int Response code.
     */
    public function getResponseCode()
    {
        return $this->_instance->getResponseCode();
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
        return $this->_instance->getAuthorizeUrl($requestToken);
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
        $this->_instance->authorize($token, $tokenSecret);
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
        try {
            $this->_instance->setAccessToken($accessToken);
        } catch (\Exception $e) {
            throw new TwitterOAuthException($e->getMessage());
        }
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
            $twUser = $this->_instance->get('account/verify_credentials');

            $twUserObj = new TwitterOAuthUser($twUser->screen_name);
            $twUserObj->setAvatarUrl($twUser->profile_image_url);
            $twUserObj->setName($twUser->name);
            $twUserObj->setLocation($twUser->location);
            $twUserObj->setProfileUrl('https://twitter.com/' . $twUser->screen_name);
            $twUserObj->setWebsite($twUser->url);
            $twUserObj->setDescription($twUser->description);
            $twUserObj->setProfileId($twUser->id);

            return $twUserObj;
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