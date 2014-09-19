<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Security\Authentication\Providers\TwitterOAuth;

use Webiny\Component\Config\Config;
use Webiny\Component\Config\ConfigObject;
use Webiny\Component\Crypt\CryptTrait;
use Webiny\Component\Http\HttpTrait;
use Webiny\Component\Security\Authentication\Providers\AuthenticationInterface;
use Webiny\Component\Security\Authentication\Providers\Login;
use Webiny\Component\Security\Token\Token;
use Webiny\Component\Security\User\UserAbstract;
use Webiny\Component\StdLib\StdLibTrait;
use Webiny\Component\TwitterOAuth\TwitterOAuthLoader;

/**
 * TwitterOAuth authentication provider.
 *
 * @package         Webiny\Component\Security\Authentication\Providers\TwitterOAuth
 */
class TwitterOAuth implements AuthenticationInterface
{

    use StdLibTrait, CryptTrait, HttpTrait;

    /**
     * @var array
     */
    private $_oauthRoles = [];

    /**
     * @var \Webiny\Component\TwitterOAuth\TwitterOAuth
     */
    private $_connection;

    /**
     * @var Config
     */
    private $_config;

    /**
     * Base constructor.
     *
     * @param string       $serverName Name of the TwitterOAuth server in the current configuration.
     * @param string|array $roles      Roles that will be set for the OAuth users.
     *
     * @throws TwitterOAuthException
     */
    function __construct($serverName, $roles)
    {
        try {
            $this->_connection = TwitterOAuthLoader::getInstance($serverName);
            $this->_oauthRoles = (array)$roles;
        } catch (\Exception $e) {
            throw new TwitterOAuthException($e->getMessage());
        }
    }

    /**
     * This method is triggered on the login submit page where user credentials are submitted.
     * On this page the provider should create a new Login object from those credentials, and return the object.
     * This object will be then validated by user providers.
     *
     * @param ConfigObject $config Firewall config
     *
     * @throws TwitterOAuthException
     * @return Login
     */
    function getLoginObject(ConfigObject $config)
    {
        // step1 -> get access token
        if (!$this->httpSession()->get('tw_oauth_token_secret', false)) {

            $requestToken = $this->_connection->getRequestToken();

            // save the session for later
            $this->httpSession()->save('tw_oauth_token', $requestToken['oauth_token']);
            $this->httpSession()->save('tw_oauth_token_secret', $requestToken['oauth_token_secret']);

            // check response code
            if ($this->_connection->getResponseCode() == 200) {
                $authUrl = $this->_connection->getAuthorizeUrl($requestToken['oauth_token']);

                header('Location: ' . $authUrl);
                die('Redirect');
            } else {
                throw new TwitterOAuthException('Could not connect to Twitter. Refresh the page or try again later.');
            }
        } else {
            $this->_connection->authorize($this->httpSession()->get('tw_oauth_token'),
                                          $this->httpSession()->get('tw_oauth_token_secret')
            );

            // request access tokens from twitter
            if ($this->httpRequest()->query('oauth_verifier', false)) {
                $access_token = $this->_connection->getAccessToken($this->httpRequest()->query('oauth_verifier'));
            } else {
                // remove no longer needed request tokens
                $this->httpSession()->delete('tw_oauth_token');
                $this->httpSession()->delete('tw_oauth_token_secret');

                // redirect back to login
                $this->httpRequest()->redirect($this->httpRequest()->getCurrentUrl());
            }

            // error check
            if ($this->_connection->getResponseCode() != 200) {
                throw new TwitterOAuthException('Could not connect to Twitter. Refresh the page or try again later.');
            }

            // save the access tokens. Normally these would be saved in a database for future use.
            $this->httpSession()->save('tw_access_token', $access_token);

            // remove no longer needed request tokens
            $this->httpSession()->delete('tw_oauth_token');
            $this->httpSession()->delete('tw_oauth_token_secret');
        }

        // step2 -> return the login object with auth token
        $login = new Login('', '');
        $login->setAttribute('tw_oauth_server', $this->_connection);
        $login->setAttribute('tw_oauth_roles', $this->_oauthRoles);

        return $login;
    }

    /**
     * This callback is triggered after we validate the given login data from getLoginObject, and the data IS NOT valid.
     * Use this callback to clear the submit data from the previous request so that you don't get stuck in an
     * infinitive loop between login page and login submit page.
     */
    function invalidLoginProvidedCallback()
    {
        // TODO: Implement invalidLoginProvidedCallback() method.
    }

    /**
     * This callback is triggered after we have validated user credentials and have created a user auth token.
     *
     * @param UserAbstract $user
     */
    function loginSuccessfulCallback(UserAbstract $user)
    {
        // TODO: Implement loginSuccessfulCallback() method.
    }

    /**
     * This callback is triggered when the system has managed to retrieve the user from the stored token (either session)
     * or cookie.
     *
     * @param UserAbstract $user
     * @param Token        $token
     *
     * @return mixed
     */
    function userAuthorizedByTokenCallback(UserAbstract $user, Token $token)
    {
        // TODO: Implement userAuthorizedByTokenCallback() method.
    }

    /**
     * Logout callback is called when user auth token was deleted.
     */
    function logoutCallback()
    {
        // TODO: Implement logoutCallback() method.
    }
}