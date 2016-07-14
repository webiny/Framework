<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Security\Tests\Mocks;

use Webiny\Component\Config\ConfigObject;
use Webiny\Component\Security\Authentication\Providers\AuthenticationInterface;
use Webiny\Component\Security\Authentication\Providers\Login;
use Webiny\Component\Security\Token\Token;
use Webiny\Component\Security\User\AbstractUser;

/**
 * Authentication provider mock
 *
 * @package         Webiny\Component\Security\Tests\Mocks
 */
class AuthenticationProviderMock implements AuthenticationInterface
{

    /**
     * This method is triggered on the login submit page where user credentials are submitted.
     * On this page the provider should create a new Login object from those credentials, and return the object.
     * This object will be then validated by user providers.
     *
     * @param ConfigObject $config Firewall config
     *
     * @return Login
     */
    public function getLoginObject(ConfigObject $config)
    {
        return new Login('username', 'password', false);
    }

    /**
     * This callback is triggered after we validate the given login data from getLoginObject, and the data IS NOT valid.
     * Use this callback to clear the submit data from the previous request so that you don't get stuck in an
     * infinitive loop between login page and login submit page.
     */
    public function invalidLoginProvidedCallback()
    {
        return null;
    }

    /**
     * This callback is triggered after we have validated user credentials and have created a user auth token.
     *
     * @param AbstractUser $user
     */
    public function loginSuccessfulCallback(AbstractUser $user)
    {
        return null;
    }

    /**
     * This callback is triggered when the system has managed to retrieve the user from the stored token (either session)
     * or cookie.
     *
     * @param AbstractUser $user
     * @param Token        $token
     *
     * @return mixed
     */
    public function userAuthorizedByTokenCallback(AbstractUser $user, Token $token)
    {
        return null;
    }

    /**
     * Logout callback is called when user auth token was deleted.
     */
    public function logoutCallback()
    {
        return null;
    }
}