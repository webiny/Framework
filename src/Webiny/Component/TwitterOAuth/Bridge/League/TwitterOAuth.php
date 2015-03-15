<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\TwitterOAuth\Bridge\League;

use League\OAuth1\Client\Credentials\TemporaryCredentials;
use League\OAuth1\Client\Credentials\TokenCredentials;
use Webiny\Component\TwitterOAuth\Bridge\TwitterOAuthInterface;
use Webiny\Component\TwitterOAuth\TwitterOAuthUser;

/**
 * Bridge for TwitterOAuth library by The PHP League (http://thephpleague.com/)
 *
 * @package         Webiny\Component\TwitterOAuth\Bridge\TwitterOAuth
 */
class TwitterOAuth implements TwitterOAuthInterface
{
    /**
     * @var null|\League\OAuth1\Client\Server\Twitter
     */
    private $instance = null;

    /**
     * OAuth2 Client ID.
     *
     * @var string
     */
    protected $clientId = '';

    /**
     * Client secret for defined Client ID.
     *
     * @var string
     */
    protected $clientSecret = '';

    /**
     * @var Array[oauth_token, oauth_token_secret]
     */
    protected $accessToken = [];



    /**
     * Base constructor.
     *
     * @param string $clientId     Client id.
     * @param string $clientSecret Client secret.
     * @param string $redirectUri  Target url where to redirect after authentication.
     */
    public function __construct($clientId, $clientSecret, $redirectUri)
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->redirectUri = $redirectUri;

        $this->instance = new \League\OAuth1\Client\Server\Twitter([
                                                                        'identifier'   => $clientId,
                                                                        'secret'       => $clientSecret,
                                                                        'callback_uri' => $redirectUri,
                                                                    ]
        );
    }

    /**
     * Get client id.
     *
     * @return string Client id.
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * Get client secret.
     *
     * @return string Client secret.
     */
    public function getClientSecret()
    {
        return $this->clientSecret;
    }

    /**
     * Get redirect uri.
     *
     * @return string Redirect uri.
     */
    public function getRedirectUri()
    {
        //NOTE: this path is automatically translated to full path on TwitterOAuthLoader class
        return $this->redirectUri;
    }

    /**
     * Get the request token (temporary credentials).
     *
     * @return array Request token [oauth_token, oauth_token_secret].
     */
    public function getRequestToken()
    {
        $credentials = $this->instance->getTemporaryCredentials();

        return [
          'oauth_token'         => $credentials->getIdentifier(),
          'oauth_token_secret'  => $credentials->getSecret()
        ];
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
        return $this->instance->getAuthorizationUrl($requestToken);
    }

    /**
     * Once we have token, we can run the authorization which than give us the option to request the access token.
     *
     * @param string $requestToken Request token returned by getRequestToken method.
     * @param string $requestTokenSecret Request token secret returned by getRequestToken method.
     * @param string $oauthToken OAuth token returned by Twitter OAuth server.
     * @param string $oauthTokenVerifier OAuth token verifier returned by Twitter OAuth server.
     *
     * @return array[oauth_token, oauth_token_secret]
     */
    public function requestAccessToken($requestToken, $requestTokenSecret, $oauthToken, $oauthTokenVerifier)
    {
        $ti = new TemporaryCredentials();
        $ti->setIdentifier($requestToken);
        $ti->setSecret($requestTokenSecret);

        $tc = $this->instance->getTokenCredentials($ti, $oauthToken, $oauthTokenVerifier);

        $token = [
            'oauth_token'           => $tc->getIdentifier(),
            'oauth_token_secret'    => $tc->getSecret()
        ];

        $this->setAccessToken($token);

        return $token;
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
        $this->accessToken = $accessToken;
    }

    /**
     * Returns the current access token.
     *
     * @return Array|bool False is returned if the access token is not set.
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * Returns an instance of TwitterOAuthUser.
     *
     * @return TwitterOAuthUser
     */
    public function getUserDetails()
    {
        $accessToken = $this->getAccessToken();
        $tc = new TokenCredentials();
        $tc->setIdentifier($accessToken['oauth_token']);
        $tc->setSecret($accessToken['oauth_token_secret']);

        $user = $this->instance->getUserDetails($tc);

        $twUserObj = new TwitterOAuthUser($user->nickname);
        $twUserObj->setAvatarUrl($user->imageUrl);
        $twUserObj->setName($user->name);
        $twUserObj->setLocation($user->location);
        $twUserObj->setProfileUrl('https://twitter.com/' . $user->screen_name);
        $twUserObj->setWebsite($user->urls['url']);
        $twUserObj->setDescription($user->description);
        $twUserObj->setProfileId($user->uid);

        return $twUserObj;
    }

    /**
     * Make a GET request to Twitter API.
     *
     * @param string $url    Api url.
     * @param array $headers Request headers.
     * @param array  $params Additional parameters.
     *
     * @return string|array Api response (if json) it will be returned as array.
     */
    public function get($url,  array $headers = [], array $params = [])
    {
        return $this->instance->createHttpClient()->get($url, $headers, $params);
    }

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
    public function post($url, array $postBody, array $headers = [], array $params = [])
    {
        return $this->instance->createHttpClient()->post($url, $headers, $postBody, $params);
    }

    /**
     * Make a DELETE request to Twitter API.
     *
     * @param string $url    Api url.
     * @param array $headers Request headers.
     * @param array  $params Additional parameters.
     *
     * @return string|array Api response (if json) it will be returned as array.
     */
    public function delete($url, array $headers = [], array $params = [])
    {
        return $this->instance->createHttpClient()->delete($url, $headers, $params);
    }
}