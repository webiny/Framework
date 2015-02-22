<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Security\Token;

use Webiny\Component\StdLib\StdLibTrait;

/**
 * Token data class holds user data that has been decrypted from token storage.
 *
 * @package         Webiny\Component\Security\Token
 */
class TokenData
{
    use StdLibTrait;

    /**
     * Users username.
     * @var string
     */
    private $_username;

    /**
     * Array of roles.
     * @var array
     */
    private $_roles;

    /**
     * Timestamp until the token data is valid.
     * @var int
     */
    private $_validUntil;

    /**
     * Name of the class that was used to provide the user authentication.
     * @var string
     */
    private $_authProviderName;


    /**
     * Base constructor.
     *
     * @param array $tokenData Decrypted token data array.
     */
    function __construct(array $tokenData)
    {
        $this->_username = $tokenData['u'];
        $this->_roles = $tokenData['r'];
        $this->_validUntil = $tokenData['vu'];
        $this->_authProviderName = $tokenData['ap'];
    }

    /**
     * Returns the username stored in token data.
     *
     * @return string
     */
    function getUsername()
    {
        return $this->_username;
    }

    /**
     * Returns the roles stored in token data.
     *
     * @return array
     */
    function getRoles()
    {
        return $this->_roles;
    }

    /**
     * Returns the name of auth provider.
     *
     * @return string
     */
    function getAuthProviderName()
    {
        return $this->_authProviderName;
    }
}