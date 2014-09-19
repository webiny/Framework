<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Security\Token;

use Webiny\Component\Crypt\CryptTrait;
use Webiny\Component\Http\HttpTrait;
use Webiny\Component\Security\Token\CryptDrivers\CryptDriverInterface;
use Webiny\Component\Security\User\UserAbstract;
use Webiny\Component\StdLib\StdLibTrait;

/**
 * Token storage abstract.
 * Every token storage class must extend this class.
 *
 * @package         Webiny\Component\Security\User\Token
 */
abstract class TokenStorageAbstract implements TokenStorageInterface
{
    use HttpTrait, CryptTrait, StdLibTrait;

    /**
     * Name of the token.
     * @var string
     */
    private $_tokenName;

    /**
     * Security key used for encrypting the token data.
     * @var string
     */
    private $_securityKey;

    /**
     * @var string Name of the crypt service.
     */
    private $_crypt;


    /**
     * This function provides the token name to the storage.
     *
     * @param string $tokenName Token name.
     */
    public function setTokenName($tokenName)
    {
        $this->_tokenName = $tokenName;
    }

    /**
     * Get token name.
     *
     * @return string Token name.
     */
    public function getTokenName()
    {
        return $this->_tokenName;
    }

    /**
     * Sets crypt driver instance.
     *
     * @param CryptDriverInterface $crypt Name of the crypt service.
     */
    public function setCrypt(CryptDriverInterface $crypt)
    {
        $this->_crypt = $crypt;
    }

    /**
     * Returns the crypt driver instance.
     *
     * @return CryptDriverInterface
     */
    public function getCrypt()
    {
        return $this->_crypt;
    }

    /**
     * Stores user data into an array, encrypts it and returns the encrypted string.
     *
     * @param UserAbstract $user Instance of UserAbstract class that holds the pre-filled object from user provider.
     *
     * @return string
     */
    public function encryptUserData(UserAbstract $user)
    {
        // data (we use short syntax to reduce the size of the cookie or session)
        $data = [
            // username
            'u'   => $user->getUsername(),
            // rules
            'r'   => $user->getRoles(),
            // valid until
            'vu'  => time() + (86400 * 30),
            // session id
            'sid' => $this->httpSession()->getSessionId(),
            // auth provider driver
            'ap'  => $user->getAuthProviderName()
        ];

        // build and add token to $data
        $token = $this->str($data['u'], '|' . $data['vu'] . '|' . $this->_securityKey)->hash()->val();
        $data['t'] = $token;

        return $this->getCrypt()->encrypt($this->serialize($data), $this->_securityKey);
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
            $data = $this->getCrypt()->decrypt($tokenData, $this->_securityKey);
            $data = $this->unserialize($data);
        } catch (\Exception $e) {
            $this->deleteUserToken();

            return false;
        }

        // validate token data
        if (!isset($data['u']) || !isset($data['r']) || !isset($data['vu']) || !isset($data['sid']) || !isset($data['t']) || !isset($data['ap'])
        ) {
            $this->deleteUserToken();

            return false;
        }

        // validate sid so we are sure that nobody stole a cookie
        if ($this->httpSession()->getSessionId() != $data['sid']) {
            $this->deleteUserToken();

            return false;
        }

        // validate token-token :)
        $token = $this->str($data['u'], '|' . $data['vu'] . '|' . $this->_securityKey)->hash()->val();
        if ($token != $data['t']) {
            $this->deleteUserToken();

            return false;
        }

        // check that token data is still valid
        if ($this->datetime()->setTimestamp($data['vu'])->isPast()) {
            $this->deleteUserToken();

            return false;
        }

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
        $this->_securityKey = $securityKey;
    }

}