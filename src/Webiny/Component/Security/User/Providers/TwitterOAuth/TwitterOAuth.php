<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Security\User\Providers\TwitterOAuth;

use Webiny\Component\EventManager\EventManagerTrait;
use Webiny\Component\Http\HttpTrait;
use Webiny\Component\Security\Authentication\Providers\Login;
use Webiny\Component\Security\User\Exceptions\UserNotFoundException;
use Webiny\Component\Security\User\UserAbstract;
use Webiny\Component\Security\User\UserProviderInterface;

/**
 * TwitterOAuth user provider
 *
 * @package         Webiny\Component\Security\User\Providers\TwitterOAuth
 */
class TwitterOAuth implements UserProviderInterface
{
    use HttpTrait, EventManagerTrait;

    /**
     * Get the user from user provided for the given instance of Login object.
     *
     * @param Login $login Instance of Login object.
     *
     * @return UserAbstract
     * @throws UserNotFoundException
     */
    public function getUser(Login $login)
    {
        // check if we have the tw_oauth_server attribute
        if (!$login->getAttribute('tw_oauth_server')) {
            throw new UserNotFoundException('User not found.');
        }

        // try to get the user from oauth
        $connection = $login->getAttribute('tw_oauth_server');
        try {
            $twUserObj = $connection->getUserDetails();

            $eventObj = new TwitterEvent($twUserObj, $connection);
            $this->eventManager()->fire(TwitterEvent::TWITTER_AUTH_SUCCESS, $eventObj);
        } catch (\Exception $e) {
            throw new UserNotFoundException($e->getMessage());
        }

        // create the user object
        $user = new User();
        $user->populate($twUserObj->getUsername(), '', $login->getAttribute('tw_oauth_roles'), true);

        return $user;
    }
}