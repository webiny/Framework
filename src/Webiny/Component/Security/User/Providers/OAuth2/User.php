<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Security\User\Providers\OAuth2;

use Webiny\Component\Security\Authentication\Firewall;
use Webiny\Component\Security\Authentication\Providers\Login;
use Webiny\Component\Security\User\UserAbstract;

/**
 * OAuth2 user class.
 *
 * @package         namespace Webiny\Component\Security\User\Providers\OAuth2
 */
class User extends UserAbstract
{

    /**
     * This method verifies the credentials of current user with the credentials provided from the Login object.
     *
     * @param Login    $login
     * @param Firewall $firewall
     *
     * @throws OAuth2Exception
     * @return bool Return true if credentials are valid, otherwise return false.
     */
    function authenticate(Login $login, Firewall $firewall)
    {
        return true; // oauth2 users are always authenticated
    }
}