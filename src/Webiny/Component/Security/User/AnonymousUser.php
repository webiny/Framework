<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Security\User;

use Webiny\Component\Security\Authentication\Providers\Login;
use Webiny\Component\Security\Encoder\Encoder;

/**
 * Anonymous user class.
 * This is the user class that is created if we cannot identify the user or when we return the user from the token.
 *
 * @package         Webiny\Component\Security\User
 */
class AnonymousUser extends UserAbstract
{

    /**
     * Base constructor.
     */
    function __construct()
    {
        parent::populate('anonymous', '', [], false);
    }

    /**
     * This method verifies the credentials of current user with the credentials provided from the Login object.
     *
     * @param Login   $login
     * @param Encoder $encoder
     *
     * @return bool Return true if credentials are valid, otherwise return false.
     */
    function authenticate(Login $login, Encoder $encoder)
    {
        return true;
    }
}