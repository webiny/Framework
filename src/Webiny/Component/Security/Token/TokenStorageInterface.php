<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Security\Token;

use Webiny\Component\Security\User\UserAbstract;

/**
 * Token storage interface.
 *
 * @package         Webiny\Component\Security\User\TokenStorage
 */
interface TokenStorageInterface
{

    /**
     * This function provides the token name to the storage.
     *
     * @param string $tokenName Token name.
     */
    public function setTokenName($tokenName);

    /**
     * Save user authentication token.
     *
     * @param UserAbstract $user Instance of UserAbstract class that holds the pre-filled object from user provider.
     *
     * @return bool
     */
    public function saveUserToken(UserAbstract $user);

    /**
     * Check if auth token is present, if true, try to load the right user and return it's username.
     *
     * @return bool|UserAbstract False it user token is not available, otherwise the UserAbstract object is returned.
     */
    public function loadUserFromToken();

    /**
     * Deletes the current auth token.
     *
     * @return bool
     */
    public function deleteUserToken();

    /**
     * Sets the security key that will be used for encryption of token data.
     *
     * @param string $securityKey Must have 16/32/64 chars.
     */
    public function setSecurityKey($securityKey);
}