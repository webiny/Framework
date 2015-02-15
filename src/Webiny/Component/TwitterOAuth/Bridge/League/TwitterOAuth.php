<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\TwitterOAuth\Bridge\League;

use League\OAuth1\Client\Credentials\TemporaryCredentials;
use Webiny\Component\TwitterOAuth\Bridge\TwitterOAuthInterface;

/**
 * Bridge for TwitterOAuth library by The PHP League (http://thephpleague.com/)
 *
 * @package         Webiny\Component\TwitterOAuth\Bridge\TwitterOAuth
 */
class TwitterOAuth implements TwitterOAuthInterface
{
    /**
     * @var null|\
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

        $this->_instance = new \League\OAuth1\Client\Server\Twitter([
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
     * Get the request token (temporary credentials).
     *
     * @return array Request token [oauth_token, oauth_token_secret].
     */
    public function getRequestToken()
    {
        $credentials = $this->_instance->getTemporaryCredentials();
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
        return $this->_instance->getAuthorizationUrl($requestToken);
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
        $ti = new TemporaryCredentials();
        $ti->setIdentifier($token);
        $ti->setSecret($tokenSecret);

        return $this->_instance->authorize($ti);
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
        $ti = $this->_instance->getTemporaryCredentials();

        return $this->_instance->getTokenCredentials($this->_instance->getTemporaryCredentials(), $ti->getIdentifier(), $verifier);
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
        return $this->authorize($accessToken['oauth_token'], $accessToken['oauth_token_secret']);
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
        return $this->_instance->createHttpClient()->get($url, $headers, $params);
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
        return $this->_instance->createHttpClient()->post($url, $headers, $postBody, $params);
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
        return $this->_instance->createHttpClient()->delete($url, $headers, $params);
    }
}