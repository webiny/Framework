<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\OAuth2\Bridge;

use Webiny\Component\StdLib\StdLibTrait;

/**
 * OAuth2 abstract class.
 * This class implements the OAuth2Interface and adds some methods that ease the implementation of OAuth2 bridge libraries.
 *
 * @package         Webiny\Component\OAuth2\Bridge
 */
abstract class AbstractOAuth2 implements OAuth2Interface
{

    use StdLibTrait;

    /**
     * Request scope, a comma-separated list of parameters.
     *
     * @var string
     */
    protected $scope = '';

    /**
     * Request state.
     *
     * @var string
     */
    protected $state = '';

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
     * A URI where the user will be redirected after OAuth2 authorization.
     *
     * @var string
     */
    protected $redirectUri = '';

    /**
     * Name of the OAuth2 server class.
     *
     * @var string
     */
    private $serverClassName = '';

    /**
     * Optional array that is provided in case of 'custom' server name.
     *
     * @var null
     */
    private $serverOptions = null;

    /**
     * Name of the access token.
     *
     * @var string
     */
    protected $accessTokenName = 'access_token';


    /**
     * Currently supported servers are:
     * [facebook, google, linkedin].
     *
     * You can also paste 'custom' as server name, but in that case you must also provide the $options array that has
     * auth_url and token_url as array keys.
     * You can put variables like {CLIENT_ID}, {REDIRECT_URI}, {SCOPE} and {STATE} inside the auth_url and this function
     * will replace them with current values.
     *
     * @param string     $serverName Name of the OAuth2 server for which you wish to get the auth_url and token_url.
     * @param null|array $options    Optional array that you must provide in case of 'custom' server name.
     */
    public function setOAuth2Server($serverName, $options = null)
    {
        $this->serverClassName = $serverName;
        $this->serverOptions = $options;
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
        $this->scope = $scope;
    }

    /**
     * Get the defined scope.
     *
     * @return string A comma separated list of parameters.
     */
    public function getScope()
    {
        return $this->scope;
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
        $this->state = $state;
    }

    /**
     * Get the state parameter.
     *
     * @return string State parameter
     */
    public function getState()
    {
        return $this->state;
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
     * Get the defined redirect URI.
     *
     * @return string Redirect URI.
     */
    public function getRedirectURI()
    {
        return $this->str($this->redirectUri)->urlDecode()->val();
    }


    /**
     * Returns the name of access token param. Its usually either 'access_token' or 'token' based on the OAuth2 server.
     *
     * @return string
     */
    public function getAccessTokenName()
    {
        return $this->accessTokenName;
    }

    /**
     * Returns the name of current OAuth2 server class.
     *
     * @return string
     */
    public function getServerClassName()
    {
        return $this->serverClassName;
    }
}