<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Security\Authentication\Providers\Form;

use Webiny\Component\Config\ConfigObject;
use Webiny\Component\Http\HttpTrait;
use Webiny\Component\Security\Authentication\Providers\AuthenticationInterface;
use Webiny\Component\Security\Authentication\Providers\Login;
use Webiny\Component\Security\Token\Token;
use Webiny\Component\Security\User\UserAbstract;

/**
 * Form authentication provider.
 * Use this provider if you have a HTML login form for your users to sign in.
 *
 * @package         Webiny\Component\Security\Authentication\Providers\Form
 */
class Form implements AuthenticationInterface
{

    use HttpTrait;

    /**
     * This method is triggered on the login submit page where user credentials are submitted.
     * On this page the provider should create a new Login object from those credentials, and return the object.
     * This object will be then validated by user providers.
     *
     * @param ConfigObject $config Firewall config
     *
     * @return Login
     */
    function getLoginObject(ConfigObject $config)
    {
        return new Login($this->httpRequest()->post('username', ''), $this->httpRequest()->post('password', ''
            ), $this->httpRequest()->post('rememberme', false)
        );
    }

    /**
     * This callback is triggered after we validate the given login data from getLoginObject, and the data IS NOT valid.
     * Use this callback to clear the submit data from the previous request so that you don't get stuck in an
     * infinitive loop between login page and login submit page.
     */
    function invalidLoginProvidedCallback()
    {
        // nothing to do...post data is not forwarded so we don't have to clear it
    }

    /**
     * This callback is triggered after we have validated user credentials and have created a user auth token.
     *
     * @param UserAbstract $user
     */
    function loginSuccessfulCallback(UserAbstract $user)
    {
        // nothing to do
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
        // nothing to do
    }

    /**
     * Logout callback is called when user auth token was deleted.
     */
    function logoutCallback()
    {
        // nothing to do
    }
}