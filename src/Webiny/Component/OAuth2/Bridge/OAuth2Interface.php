<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\OAuth2\Bridge;

/**
 * OAuth2 bridge interface.
 * All of OAuth2 bridges must implement this interface in order to be compliant with WebinyFramework.
 *
 * @package         Webiny\Component\OAuth2\Bridge
 */

interface OAuth2Interface
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
     * Get client id.
     *
     * @return string Client id.
     */
    public function getClientId();

    /**
     * Get client secret.
     *
     * @return string Client secret.
     */
    public function getClientSecret();

    /**
     * Requests the access token from the OAuth server.
     * You can call this method only on the OAuth redirect_uri page or else the request will fail.
     *
     * @param string $tokenUrl Url to the page where we can get the access token.
     *
     * @throws \Webiny\Component\OAuth2\Bridge\OAuth2Exception
     * @return string Access token.
     */
    public function requestAccessToken($tokenUrl);

    /**
     * Get access  token.
     *
     * @return string Access token.
     */
    public function getAccessToken();


    /**
     * Get the defined redirect URI.
     *
     * @return string Redirect URI.
     */
    public function getRedirectURI();

    /**
     * Set the access token.
     *
     * @param string $accessToken Access token.
     *
     * @return void
     */
    public function setAccessToken($accessToken);

    /**
     * Set the certificate used by OAuth2 requests.
     *
     * @param string $pathToCertificate Absolute path to the certificate file.
     *
     * @return void
     */
    public function setCertificate($pathToCertificate);

    /**
     * Returns the path to certificate.
     *
     * @return string Path to certificate.
     */
    public function getCertificate();

    /**
     * Set the request scope.
     *
     * @param string $scope A comma-separated list of parameters. Example: email,extender_permissions
     *
     * @return void
     */
    public function setScope($scope);

    /**
     * Get the defined scope.
     *
     * @return string A comma separated list of parameters.
     */
    public function getScope();

    /**
     * Set the state parameter.
     *
     * @param string $state State name.
     *
     * @return void.
     */
    public function setState($state);

    /**
     * Get the state parameter.
     *
     * @return string State parameter
     */
    public function getState();

    /**
     * Returns the name of access token param. Its usually either 'access_token' or 'token' based on the OAuth2 server.
     *
     * @return string
     */
    public function getAccessTokenName();
}