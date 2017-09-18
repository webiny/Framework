<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Security\Tests\Mocks;

use Webiny\Component\Security\Token\AbstractTokenStorage;
use Webiny\Component\Security\User\AbstractUser;

/**
 * Token storage mock
 *
 * @package         Webiny\Component\Security\Tests\Mocks
 */
class TokenStorageMock extends AbstractTokenStorage
{

    /**
     * Save user authentication token.
     *
     * @param AbstractUser $user Instance of AbstractUser class that holds the pre-filled object from user provider.
     *
     * @return bool
     */
    public function saveUserToken(AbstractUser $user)
    {
        return true;
    }

    /**
     * Check if auth token is present, if true, try to load the right user and return it's username.
     *
     * @return bool|AbstractUser False it user token is not available, otherwise the AbstractUser object is returned.
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

    /**
     * Get token string representation
     * @return string
     */
    public function getTokenString()
    {
        return '';
    }

    /**
     * Save the provided token string into the token storage.
     *
     * @param string $token Token string to save.
     */
    public function setTokenString($token)
    {
        //
    }

    public function getTokenTtl()
    {
        return 0;
    }
}