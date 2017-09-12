<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Crypt;

use Webiny\Component\Crypt\Bridge\CryptInterface;
use Webiny\Component\StdLib\ComponentTrait;
use Webiny\Component\StdLib\StdLibTrait;

/**
 * This is a class for simple cryptographic functions in PHP.
 *
 * @package         Webiny\Component\Crypt
 */
class Crypt
{
    use ComponentTrait, StdLibTrait;

    /**
     * @var null|CryptInterface
     */
    private $driverInstance = null;

    /**
     * Base constructor.
     *
     * @throws CryptException
     */
    public function __construct()
    {
        if ($this->isNull($this->driverInstance)) {
            try {
                $this->driverInstance = Bridge\Crypt::getInstance();

                if (!$this->isInstanceOf($this->driverInstance, CryptInterface::class)) {
                    throw new CryptException('The provided bridge does not implement the required interface "' . CryptInterface::class . '"');
                }
            } catch (\Exception $e) {
                throw $e;
            }
        }
    }

    /**
     * Generates a random integer between the given $min and $max values.
     *
     * @param int $min Lower limit.
     * @param int $max Upper limit
     *
     * @throws CryptException
     * @return int Random number between $min and $max.
     */
    public function generateRandomInt($min, $max)
    {
        try {
            return $this->driverInstance->generateRandomInt($min, $max);
        } catch (\Exception $e) {
            throw new CryptException($e->getMessage());
        }
    }

    /**
     * Generates a random string using the defined character set.
     * If $chars param is empty, the string will be generated using numbers, letters and special characters.
     *
     * @param int    $length Length of the generated string.
     * @param string $chars A string containing a list of chars that will be uses for generating the random string.
     *
     * @throws CryptException
     * @return string Random string with the given $length containing only the provided set of $chars.
     */
    public function generateRandomString($length, $chars = '')
    {
        try {
            return $this->driverInstance->generateRandomString($length, $chars);
        } catch (\Exception $e) {
            throw new CryptException($e->getMessage());
        }
    }

    /**
     * Generates a random string, but without using special characters that are hard to read.
     * This method is ok to use for generating random user passwords. (which, of course, should be changed after first login).
     *
     * @param int $length Length of the random string.
     *
     * @throws CryptException
     * @return string Random string with the given $length.
     */
    public function generateUserReadableString($length)
    {
        try {
            return $this->driverInstance->generateUserReadableString($length);
        } catch (\Exception $e) {
            throw new CryptException($e->getMessage());
        }
    }

    /**
     * Generates a random string with a lot of 'noise' (special characters).
     * Use this method to generate API keys, salts and similar.
     *
     * @param int $length Length of the random string.
     *
     * @throws CryptException
     * @return string Random string with the given $length.
     */
    public function generateHardReadableString($length)
    {
        try {
            return $this->driverInstance->generateHardReadableString($length);
        } catch (\Exception $e) {
            throw new CryptException($e->getMessage());
        }
    }

    // password hashing and verification

    /**
     * Creates a hash from the given $password string.
     * The hashing algorithm used depends on your config.
     *
     * @param string $password String you wish to hash.
     *
     * @throws CryptException
     * @return string Hash of the given string.
     */
    public function createPasswordHash($password)
    {
        try {
            return $this->driverInstance->createPasswordHash($password);
        } catch (\Exception $e) {
            throw new CryptException($e->getMessage());
        }
    }

    /**
     * Verify if the given $hash matches the given $password.
     *
     * @param string $password Original, un-hashed, password.
     * @param string $hash Hash string to which the check should be made
     *
     * @throws CryptException
     * @return bool True if $password matches the $hash, otherwise false is returned.
     */
    public function verifyPasswordHash($password, $hash)
    {
        try {
            return $this->driverInstance->verifyPasswordHash($password, $hash);
        } catch (\Exception $e) {
            throw new CryptException($e->getMessage());
        }
    }

    // encryption and decryption

    /**
     * Encrypt the given $string using a cypher and the secret $key
     *
     * @param string $string The string you want to encrypt.
     * @param string $key The secret key that will be used to encrypt the string.
     *
     * @throws CryptException
     *
     * @return string Encrypted string.
     */
    public function encrypt($string, $key)
    {
        try {
            return $this->driverInstance->encrypt($string, $key);
        } catch (\Exception $e) {
            throw new CryptException($e->getMessage());
        }
    }

    /**
     * Decrypt a string that has been encrypted with the 'encrypt' method.
     * In order to decrypt the string correctly, you must provide the same secret key that was used for the encryption
     * process.
     *
     * @param string $string The string you want to decrypt.
     * @param string $key The secret key that was used to encrypt the $string.
     *
     * @throws CryptException
     * @return string Decrypted string or false if unable to decrypt (wrong key).
     */
    public function decrypt($string, $key)
    {
        try {
            return $this->driverInstance->decrypt($string, $key);
        } catch (\Exception $e) {
            throw new CryptException($e->getMessage());
        }
    }
}
