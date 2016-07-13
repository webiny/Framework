<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Security\Tests\Mocks;

use Webiny\Component\Security\Authentication\Providers\Login;
use Webiny\Component\Security\User\Exceptions\UserNotFoundException;
use Webiny\Component\Security\User\AbstractUser;
use Webiny\Component\Security\User\UserProviderInterface;

/**
 * User provider mock
 *
 * @package         Webiny\Component\Security\Tests\Mocks
 */
class UserProviderMock implements UserProviderInterface
{

    /**
     * Using this static attribute we can change the return of the getUser method, enabling us to test different cases.
     * @var bool
     */
    public static $returnLoginObject = true;

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
        if (self::$returnLoginObject) {
            $user = new UserMock();
            $user->populate($login->getUsername(), $login->getPassword(), ['ROLE_MOCK'], false);

            return $user;
        }

        return false;

    }
}