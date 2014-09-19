<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Security\Tests\Mocks;

use Webiny\Component\Security\Token\TokenStorageAbstract;
use Webiny\Component\Security\User\UserAbstract;

/**
 * Token storage mock
 *
 * @package         Webiny\Component\Security\Tests\Mocks
 */
class TokenStorageMock extends TokenStorageAbstract
{

    /**
     * Save user authentication token.
     *
     * @param UserAbstract $user Instance of UserAbstract class that holds the pre-filled object from user provider.
     *
     * @return bool
     */
    public function saveUserToken(UserAbstract $user)
    {
        return true;
    }

    /**
     * Check if auth token is present, if true, try to load the right user and return it's username.
     *
     * @return bool|UserAbstract False it user token is not available, otherwise the UserAbstract object is returned.
     */
    public function loadUserFromToken()
    {
        return false;
    }

    /**
     * Deletes the current auth token.
     *
     * @return bool
     */
    public function deleteUserToken()
    {
        return true;
    }
}