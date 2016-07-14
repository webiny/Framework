<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\OAuth2;

use Webiny\Component\OAuth2\Bridge\AbstractOAuth2;
use Webiny\Component\StdLib\ComponentTrait;

/**
 * OAuth2 component.
 *
 * This component is used for communication with OAuth2 servers like Facebook Graph API.
 *
 * @package         Webiny\Component\OAuth2
 */
class OAuth2
{
    use ComponentTrait;

    /**
     * @var AbstractOAuth2
     */
    private $instance;

    /**
     * @var AbstractServer
     */
    private $server;


    /**
     * Base constructor.
     * NOTE: Use OAuth2Loader::getInstance method to get an OAuth2 instance.
     *
     * @param AbstractOAuth2 $instance
     */
    public function __construct(AbstractOAuth2 $instance)
    {
        $this->instance = $instance;

        $server = $instance->getServerClassName();
        $this->server = new $server($this);

    }

    /**
     * @return AbstractServer
     */
    public function request()
    {
        return $this->server;
    }

    /**
     * Get client id.
     *
     * @return string Client id.
     */
    public function getClientId()
    {
        return $this->instance->getClientId();
    }

    /**
     * Get client secret.
     *
     * @return string Client secret.
     */
    public function getClientSecret()
    {
        return $this->instance->getClientSecret();
    }

    /**
     * Requests the access token from the OAuth server.
     * You can call this method only on the OAuth redirect_uri page or else the request will fail.
     *
     * @throws \Webiny\Component\OAuth2\Bridge\OAuth2Exception
     * @return string Access token.
     */
    public function requestAccessToken()
    {
        $tokenUrl = $this->processUrl($this->server->getAccessTokenUrl());
        $accessToken = $this->instance->requestAccessToken($tokenUrl);
        $this->instance->setAccessToken($accessToken);

        return $accessToken;
    }

    /**
     * Get access  token.
     *
     * @return string Access token.
     */
    public function getAccessToken()
    {
        return $this->instance->getAccessToken();
    }

    /**
     * Get the defined redirect URI.
     *
     * @return string Redirect URI.
     */
    public function getRedirectURI()
    {
        return $this->instance->getRedirectURI();
    }

    /**
     * Set the access token.
     *
     * @param string $accessToken Access token.
     *
     * @return void
     */
    public function setAccessToken($accessToken)
    {
        $this->instance->setAccessToken($accessToken);
    }

    /**
     * Set the certificate used by OAuth2 requests.
     *
     * @param string $pathToCertificate Absolute path to the certificate file.
     *
     * @return void
     */
    public function setCertificate($pathToCertificate)
    {
        $this->instance->setCertificate($pathToCertificate);
    }

    /**
     * Returns the path to certificate.
     *
     * @return string Path to certificate.
     */
    public function getCertificate()
    {
        return $this->instance->getCertificate();
    }

    /**
     * Set the request scope.
     *
     * @param string $scope A comma-separated list of parameters. Example: email,extender_permissions
     *
     * @return void
     */
    public function setScope($scope)
    {
        $this->instance->setScope($scope);
    }

    /**
     * Get the defined scope.
     *
     * @return string A comma separated list of parameters.
     */
    public function getScope()
    {
        return $this->instance->getScope();
    }

    /**
     * Set the state parameter.
     *
     * @param string $state State name.
     *
     * @return void.
     */
    public function setState($state)
    {
        $this->instance->setState($state);
    }

    /**
     * Get the state parameter.
     *
     * @return string State parameter
     */
    public function getState()
    {
        return $this->instance->getState();
    }

    /**
     * Returns the name of access token param. Its usually either 'access_token' or 'token' based on the OAuth2 server.
     *
     * @return string
     */
    public function getAccessTokenName()
    {
        return $this->instance->getAccessTokenName();
    }

    /**
     * Returns the authentication url.
     *
     * @return string Authentication url
     */
    public function getAuthenticationUrl()
    {
        return $this->processUrl($this->server->getAuthorizeUrl());
    }

    /**
     * Replaces the url variables with real data.
     *
     * @param string $url Url to process.
     *
     * @return string Processed url.
     */
    private function processUrl($url)
    {
        $vars = [
            '{CLIENT_ID}'    => $this->getClientId(),
            '{REDIRECT_URI}' => $this->getRedirectURI(),
            '{SCOPE}'        => $this->getScope(),
            '{STATE}'        => $this->getState(),
            " "              => '',
            "\n"             => '',
            "\r"             => '',
            "\t"             => ''
        ];

        $url = str_replace(array_keys($vars), array_values($vars), $url);

        return $url;
    }
}