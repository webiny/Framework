<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Security\User\Providers\OAuth2;

use Webiny\Component\EventManager\EventManagerTrait;
use Webiny\Component\Http\HttpTrait;
use Webiny\Component\Security\Authentication\Providers\Login;
use Webiny\Component\Security\User\Exceptions\UserNotFoundException;
use Webiny\Component\Security\User\AbstractUser;
use Webiny\Component\Security\User\UserProviderInterface;

/**
 * OAuth2 user provider
 *
 * @package         Webiny\Component\Security\User\Providers\OAuth2
 */
class OAuth2 implements UserProviderInterface
{
    use HttpTrait, EventManagerTrait;

    /**
     * Get the user from user provided for the given instance of Login object.
     *
     * @param Login $login Instance of Login object.
     *
     * @return AbstractUser
     * @throws UserNotFoundException
     */
    public function getUser(Login $login)
    {
        // check if we have the oauth_server attribute
        if (!$login->getAttribute('oauth2_server')) {
            throw new UserNotFoundException('User not found.');
        }
        // try to get the user from oauth
        $oauth2 = $login->getAttribute('oauth2_server');
        try {
            $oauth2User = $oauth2->request()->getUserDetails();

            // fire the event
            $eventClass = new OAuth2Event($oauth2User, $oauth2);
            $this->eventManager()->fire(OAuth2Event::OAUTH2_AUTH_SUCCESS, $eventClass);
        } catch (\Exception $e) {
            $this->httpSession()->delete('oauth_token');
            throw new UserNotFoundException($e->getMessage());
        }

        // create the user object
        $user = new User();
        $user->populate($oauth2User->email, '', $login->getAttribute('oauth2_roles'), true);

        return $user;
    }
}