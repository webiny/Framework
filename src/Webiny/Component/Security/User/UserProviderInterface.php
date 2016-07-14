<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Security\User;

use Webiny\Component\Security\Authentication\Providers\Login;
use Webiny\Component\Security\User\Exceptions\UserNotFoundException;

/**
 * User provider interface.
 * Every user provider must implement this interface.
 *
 * @package         Webiny\Component\Security\User
 */
interface UserProviderInterface
{

    /**
     * Get the user from user provided for the given instance of Login object.
     *
     * @param Login $login Instance of Login object.
     *
     * @return AbstractUser
     * @throws UserNotFoundException
     */
    public function getUser(Login $login);
}
