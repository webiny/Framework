<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Security\Token\Storage;

use Webiny\Component\Crypt\CryptTrait;
use Webiny\Component\Http\HttpTrait;
use Webiny\Component\Security\Token\AbstractTokenStorage;
use Webiny\Component\Security\User\AbstractUser;

/**
 * Session token storage.
 *
 * @package         Webiny\Component\Security\User\Token\Storage
 */
class Session extends AbstractTokenStorage
{

    use HttpTrait;

    /**
     * Save user authentication token.
     *
     * @param AbstractUser $user Instance of AbstractUser class that holds the pre-filled object from user provider.
     *
     * @return bool
     */
    public function saveUserToken(AbstractUser $user)
    {
        return $this->httpSession()->save($this->getTokenName(), $this->encryptUserData($user));
    }

    /**
     * Check if auth token is present, if true, try to load the right user and return it's username.
     *
     * @return bool|AbstractUser False it user token is not available, otherwise the AbstractUser object is returned.
     */
    public function loadUserFromToken()
    {
        $token = $this->httpSession()->get($this->getTokenName());
        if (!$token) {
            return false;
        }

        return $this->decryptUserData($token);
    }

    /**
     * Deletes the current auth token.
     *
     * @return bool
     */
    public function deleteUserToken()
    {
        return $this->httpSession()->delete($this->getTokenName());
    }

    /**
     * Get token string representation
     * @return string
     */
    public function getTokenString()
    {
        $this->httpSession()->get($this->getTokenName());
    }

    /**
     * Save the provided token string into the token storage.
     *
     * @param string $token Token string to save.
     */
    public function setTokenString($token)
    {
        $this->httpSession()->save($this->getTokenName(), $token);
    }
}