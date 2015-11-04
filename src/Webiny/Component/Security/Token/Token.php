<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Security\Token;

use Webiny\Component\Security\Token\CryptDrivers\CryptDriverInterface;
use Webiny\Component\Security\User\UserAbstract;
use Webiny\Component\StdLib\Exception\Exception;
use Webiny\Component\StdLib\FactoryLoaderTrait;

/**
 * Token
 *
 * @package         Webiny\Component\Security\User\Token
 */
class Token
{
    use FactoryLoaderTrait;

    // store token into cookie -> only if remember me is TRUE
    const TOKEN_COOKIE_STORAGE = '\Webiny\Component\Security\Token\Storage\Cookie';
    // store token into session -> only if remember me is FALSE
    const TOKEN_SESSION_STORAGE = '\Webiny\Component\Security\Token\Storage\Session';

    /**
     * @var TokenStorageAbstract
     */
    private $storage;

    /**
     * @var bool
     */
    private $rememberMe = false;

    /**
     * Base constructor.
     *
     * @param string                            $tokenName   Name of the token.
     * @param bool                              $rememberMe  Do you want to store the token into cookie, or not. If you don't store it into cookie, the
     *                                                       token is only valid for current session.
     * @param string                            $securityKey Security key that will be used for encryption of token data
     * @param CryptDrivers\CryptDriverInterface $cryptDriver
     * @param null|string                       $storageClass
     *
     * @throws TokenException
     * @internal param \Webiny\Component\Security\Token\Crypt\CryptDriverInterface $crypt Name of the crypt driver that we be used to encode the session/cookie.
     *
     */
    public function __construct(
        $tokenName,
        $rememberMe = false,
        $securityKey,
        CryptDriverInterface $cryptDriver,
        $storageClass = null
    ) {

        $this->rememberMe = $rememberMe;

        try {
            if (!$storageClass) {
                $storageClass = $this->getStorageName();
            }
            $this->storage = $this->factory($storageClass, '\Webiny\Component\Security\Token\TokenStorageAbstract');
            $this->storage->setSecurityKey($securityKey);
            $this->storage->setCrypt($cryptDriver);
        } catch (Exception $e) {
            throw new TokenException($e->getMessage());
        }

        $this->storage->setTokenName($tokenName);
        $this->storage->setTokenRememberMe($this->rememberMe);
    }

    /**
     * Get string representation of token
     *
     * @return string
     */
    public function getTokenString()
    {
        return $this->storage->getTokenString();
    }

    /**
     * Save the provided token string into the token storage.
     *
     * @param string $token Token string to save.
     */
    public function setTokenString($token)
    {
        $this->storage->setTokenString($token);
    }

    /**
     * Should token be remembered or not
     *
     * @param bool $rememberMe
     * @return $this
     */
    public function setRememberMe($rememberMe)
    {
        $this->rememberMe = $rememberMe;
        $this->storage->setTokenRememberMe($rememberMe);

        return $this;
    }

    /**
     * Tries to load current user from token and if succeeds, an instance of TokenData is returned.
     *
     * @return bool|TokenData Instance of TokenData is returned is the token exists, otherwise false is returned.
     */
    public function getUserFromToken()
    {
        return $this->storage->loadUserFromToken();
    }

    /**
     * Creates a token for the given $user.
     *
     * @param UserAbstract $user Instance of UserAbstract class that holds the pre-filled object from user provider.
     *
     * @return bool
     */
    public function saveUser(UserAbstract $user)
    {
        return $this->storage->saveUserToken($user);
    }

    /**
     * Deletes current token.
     *
     * @return bool
     */
    public function deleteUserToken()
    {
        return $this->storage->deleteUserToken();
    }

    /**
     * Returns the correct storage name. If 'rememberMe' is true, Cookie storage is returned, otherwise
     * Session storage is returned.
     *
     * @return string
     */
    private function getStorageName()
    {
        if ($this->rememberMe) {
            return self::TOKEN_COOKIE_STORAGE;
        }

        return self::TOKEN_SESSION_STORAGE;
    }
}