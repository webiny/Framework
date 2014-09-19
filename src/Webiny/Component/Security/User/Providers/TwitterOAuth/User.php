<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Security\User\Providers\TwitterOAuth;

use Webiny\Component\Security\Authentication\Providers\Login;
use Webiny\Component\Security\Encoder\Encoder;
use Webiny\Component\Security\User\UserAbstract;

/**
 * TwitterOauth user class.
 *
 * @package    Webiny\Component\Security\User\Providers\TwitterOAuth
 */
class User extends UserAbstract
{

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
        return true; // twitter oauth users are always authenticated
    }
}