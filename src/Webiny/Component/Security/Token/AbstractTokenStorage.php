<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Security\Token;

use Webiny\Component\Crypt\CryptTrait;
use Webiny\Component\Http\HttpTrait;
use Webiny\Component\Security\Role\Role;
use Webiny\Component\Security\Token\CryptDrivers\CryptDriverInterface;
use Webiny\Component\Security\User\AbstractUser;
use Webiny\Component\StdLib\StdLibTrait;

/**
 * Token storage abstract.
 * Every token storage class must extend this class.
 *
 * @package         Webiny\Component\Security\User\Token
 */
abstract class AbstractTokenStorage implements TokenStorageInterface
{
    use HttpTrait, CryptTrait, StdLibTrait;

    /**
     * Name of the token.
     * @var string
     */
    protected $tokenName;

    /**
     * Should token be remembered
     * @var bool
     */
    protected $tokenRememberMe;

    /**
     * Security key used for encrypting the token data.
     * @var string
     */
    protected $securityKey;

    /**
     * @var string Name of the crypt service.
     */
    protected $crypt;


    /**
     * This function provides the token name to the storage.
     *
     * @param string $tokenName Token name.
     */
    public function setTokenName($tokenName)
    {
        $this->tokenName = $tokenName;
    }

    /**
     * Get token name.
     *
     * @return string Token name.
     */
    public function getTokenName()
    {
        return $this->tokenName;
    }

    /**
     * This function provides the token 'remember me' flag to the storage.
     *
     * @param bool $rememberMe Token rememberme.
     */
    public function setTokenRememberMe($rememberMe){
        $this->tokenRememberMe = $rememberMe;
    }

    /**
     * Sets crypt driver instance.
     *
     * @param CryptDriverInterface $crypt Name of the crypt service.
     */
    public function setCrypt(CryptDriverInterface $crypt)
    {
        $this->crypt = $crypt;
    }

    /**
     * Returns the crypt driver instance.
     *
     * @return CryptDriverInterface
     */
    public function getCrypt()
    {
        return $this->crypt;
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
        // extract the roles
        $roles = $user->getRoles();
        $roleArray = [];
        foreach($roles as $r){
            $roleArray[] = $r->getRole();
        }

        // data (we use short syntax to reduce the size of the cookie or session)
        $data = [
            // username
            'u'   => $user->getUsername(),
            // roles
            'r'   => $roleArray,
            // valid until
            'vu'  => time() + (86400 * 30),
            // session id
            'sid' => $this->httpSession()->getSessionId(),
            // auth provider driver
            'ap'  => $user->getAuthProviderName()
        ];

        // build and add token to $data
        return $this->getCrypt()->encrypt($this->jsonEncode($data), $this->getEncryptionKey());
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
            $data = $this->getCrypt()->decrypt($tokenData, $this->getEncryptionKey());
            $data = $this->jsonDecode($data, true);
        } catch (\Exception $e) {
            $this->deleteUserToken();

            return false;
        }

        // validate token data
        if (!isset($data['u']) || !isset($data['r']) || !isset($data['vu']) || !isset($data['sid']) || !isset($data['ap'])
        ) {
            $this->deleteUserToken();

            return false;
        }

        // validate sid
        if ($this->httpSession()->getSessionId() != $data['sid']) {
            $this->deleteUserToken();

            return false;
        }

        // check that token data is still valid
        if ($this->datetime()->setTimestamp($data['vu'])->isPast()) {
            $this->deleteUserToken();

            return false;
        }

        // recreate the roles
        $roles = [];
        foreach($data['r'] as $role){
            $roles[] = new Role($role);
        }
        $data['r'] = $roles;

        // return TokenData instance
        return new TokenData($data);
    }

    /**
     * Sets the security key that will be used for encryption of token data.
     *
     * @param string $securityKey Must have 16/32/64 chars.
     */
    public function setSecurityKey($securityKey)
    {
        $this->securityKey = $securityKey;
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

        // append current session id
        $securityKey .= $this->httpSession()->getSessionId();

        // append user agent
        $securityKey .= $this->httpRequest()->server()->httpUserAgent();

        // hash and return
        return hash('sha512', $securityKey);
    }
}