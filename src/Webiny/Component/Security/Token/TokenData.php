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
    private $username;

    /**
     * Array of roles.
     * @var array
     */
    private $roles;

    /**
     * Timestamp until the token data is valid.
     * @var int
     */
    private $validUntil;

    /**
     * Name of the class that was used to provide the user authentication.
     * @var string
     */
    private $authProviderName;


    /**
     * Base constructor.
     *
     * @param array $tokenData Decrypted token data array.
     */
    public function __construct(array $tokenData)
    {
        $this->username = $tokenData['u'];
        $this->roles = $tokenData['r'];
        $this->validUntil = $tokenData['vu'];
        $this->authProviderName = $tokenData['ap'];
    }

    /**
     * Returns the username stored in token data.
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Returns the roles stored in token data.
     *
     * @return array
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * Returns the name of auth provider.
     *
     * @return string
     */
    public function getAuthProviderName()
    {
        return $this->authProviderName;
    }
}