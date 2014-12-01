<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Security\User\Providers\Memory;

use Webiny\Component\Security\Authentication\Firewall;
use Webiny\Component\Security\Authentication\Providers\Login;
use Webiny\Component\Security\User\UserAbstract;

/**
 * Memory user provider - user class
 *
 * @package         Webiny\Component\Security\User\Providers\Memory
 */
class User extends UserAbstract
{
    /**
     * This method verifies the credentials of current user with the credentials provided from the Login object.
     *
     * @param Login   $login
     * @param Firewall $firewall
     *
     * @throws MemoryException
     * @return bool Return true if credentials are valid, otherwise return false.
     */
    function authenticate(Login $login, Firewall $firewall)
    {
        try {
            $result = $firewall->verifyPasswordHash($login->getPassword(), $this->getPassword());
        } catch (\Exception $e) {
            throw new MemoryException($e->getMessage());
        }

        return $result;
    }
}