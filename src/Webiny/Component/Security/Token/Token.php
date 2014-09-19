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
 * Token abstract.
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
    private $_storage;

    /**
     * @var bool
     */
    private $_rememberMe = false;

    /**
     * Base constructor.
     *
     * @param string                            $tokenName   Name of the token.
     * @param bool                              $rememberMe  Do you want to store the token into cookie, or not. If you don't store it into cookie, the
     *                                                       token is only valid for current session.
     * @param string                            $securityKey Security key that will be used for encryption of token data
     * @param CryptDrivers\CryptDriverInterface $cryptDriver
     *
     * @throws TokenException
     * @internal param \Webiny\Component\Security\Token\Crypt\CryptDriverInterface $crypt Name of the crypt driver that we be used to encode the session/cookie.
     *
     */
    public function __construct($tokenName, $rememberMe = false, $securityKey, CryptDriverInterface $cryptDriver)
    {

        $this->_rememberMe = $rememberMe;

        try {
            $this->_storage = $this->factory($this->_getStorageName(),
                                             '\Webiny\Component\Security\Token\TokenStorageAbstract'
            );
            $this->_storage->setSecurityKey($securityKey);
            $this->_storage->setCrypt($cryptDriver);
        } catch (Exception $e) {
            throw new TokenException($e->getMessage());
        }

        $this->_storage->setTokenName($tokenName);
    }

    /**
     * Tries to load current user from token and if succeeds, an instance of TokenData is returned.
     *
     * @return bool|TokenData Instance of TokenData is returned is the token exists, otherwise false is returned.
     */
    public function getUserFromToken()
    {
        return $this->_storage->loadUserFromToken();
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
        return $this->_storage->saveUserToken($user);
    }

    /**
     * Deletes current token.
     *
     * @return bool
     */
    public function deleteUserToken()
    {
        return $this->_storage->deleteUserToken();
    }

    /**
     * Returns the correct storage name. If 'rememberMe' is true, Cookie storage is returned, otherwise
     * Session storage is returned.
     *
     * @return string
     */
    private function _getStorageName()
    {
        if ($this->_rememberMe) {
            return self::TOKEN_COOKIE_STORAGE;
        }

        return self::TOKEN_SESSION_STORAGE;
    }
}