<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Security\Token\Storage;

use Webiny\Component\Http\HttpTrait;
use Webiny\Component\Security\Token\TokenData;
use Webiny\Component\Security\Token\TokenException;
use Webiny\Component\Security\Token\AbstractTokenStorage;
use Webiny\Component\Security\User\AbstractUser;

/**
 * Stateless token storage.
 * It does not store the token anywhere, to make it stateless.
 * All the necessary data to authorize the bearer is encrypted in the token itself.
 *
 * @package         Webiny\Component\Security\User\Token\Storage
 */
class Stateless extends AbstractTokenStorage
{
    use HttpTrait;

    private $tokenString = '';

    /**
     * Save user authentication token.
     *
     * @param AbstractUser $user Instance of AbstractUser class that holds the pre-filled object from user provider.
     *
     * @return bool
     */
    public function saveUserToken(AbstractUser $user)
    {
        $this->tokenString = $this->encryptUserData($user);

        return true;
    }

    /**
     * Check if auth token is present, if true, try to load the right user and return it's username.
     *
     * @return bool|AbstractUser False it user token is not available, otherwise the AbstractUser object is returned.
     */
    public function loadUserFromToken()
    {
        $token = $this->getTokenString();
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
        return true;
    }

    /**
     * Stores user data into an array, encrypts it and returns the encrypted string.
     *
     * @param AbstractUser $user Instance of AbstractUser class that holds the pre-filled object from user provider.
     *
     * @return string
     */
    public function encryptUserData(AbstractUser $user)
    {
        $seconds = 86400; // 1 day
        if ($this->tokenRememberMe) {
            $seconds = is_numeric($this->tokenRememberMe) ? intval($this->tokenRememberMe) : 2592000; // 30 days
        }

        // data (we use short syntax to reduce the size of the cookie or session)
        $data = [
            // username
            'u'  => $user->getUsername(),
            // valid until
            'vu' => time() + $seconds,
            // auth provider driver
            'ap' => $user->getAuthProviderName(),
            // user provider driver
            'up' => $user->getUserProviderName()
        ];

        // build and add token to $data
        $token = $this->getCrypt()->encrypt($this->jsonEncode($data), $this->getEncryptionKey());
        $token = urlencode(rtrim($token, '='));

        return $token;
    }

    /**
     * Decrypts the provided $tokenData, unserializes the string, creates an instance of TokenData and validates it.
     * If TokenData is valid, its instance is returned, otherwise false is returned.
     *
     * @param string $tokenData Encrypted data.
     *
     * @return TokenData|bool
     * @throws TokenException
     */
    public function decryptUserData($tokenData)
    {
        // decrypt token data
        try {
            $tokenData = urldecode($tokenData);
            $tokenData .= '==';
            $data = $this->getCrypt()->decrypt($tokenData, $this->getEncryptionKey());
            $data = $this->jsonDecode($data, true);
        } catch (\Exception $e) {
            return false;
        }

        // validate token data
        $keys = ['u', 'vu', 'up', 'ap'];
        if (!$this->arr($data)->keysExist($keys)) {
            return false;
        }

        // check that token data is still valid
        if ($this->datetime($data['vu'])->isPast()) {
            return false;
        }

        return new TokenData($data);
    }

    /**
     * Uses the current key, user session id and browser user agent, to form a new key.
     * The new key is then unique to that user, and is used for encryption/decryption process.
     *
     * @return string
     */
    public function getEncryptionKey()
    {
        // initial key
        $securityKey = $this->securityKey;

        // hash and return
        return hash('sha512', $securityKey);
    }

    /**
     * Get token string representation
     * @return string
     */
    public function getTokenString()
    {
        if (!$this->tokenString) {
            $token = $this->httpRequest()->header('Authorization');
            if (!$token) {
                $token = $this->httpRequest()->post('Authorization');
            }
            return $token;
        }

        return $this->tokenString;
    }

    /**
     * Save the provided token string into the token storage.
     *
     * @param string $token Token string to save.
     */
    public function setTokenString($token)
    {
        $this->tokenString = $token;
    }
}
