<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Crypt\Bridge\Openssl;

use ParagonIE\Halite\HiddenString;
use ParagonIE\Halite\KeyFactory;
use ParagonIE\Halite\Symmetric\Crypto;
use ParagonIE\Halite\Symmetric\EncryptionKey;
use Webiny\Component\Crypt\Bridge\CryptInterface;
use Webiny\Component\Security\Token\CryptDrivers\Crypt\CryptException;

/**
 * Class Crypt.
 *
 * This is a simple class providing the basic cryptographic methods.
 *
 * It's using libsodium instead of old mcrypt, via paragonie/halite PHP package.
 * @package Webiny\Component\Crypt\Bridge\Webiny
 */
class Crypt implements CryptInterface
{
    const CHARS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    const NUMBERS = '0123456789';
    const SYMBOLS = '!"#$%&\'()* +,-./:;<=>?@[\]^_`{|}~';
    const HASH = 'sha256';
    const METHOD = 'aes-256-cbc';

    /**
     * Encrypt the given $string using a cypher and the secret $key
     *
     * @param string $string The string you want to encrypt.
     * @param string $key The secret key that will be used to encrypt the string.
     *
     * @return string|bool Encrypted string. False if encryption fails.
     */
    public function encrypt($string, $key)
    {
        try {
            $key = hash(self::HASH, $key);
            $iv = openssl_random_pseudo_bytes(16);

            // encrypt the message
            $cipherText = openssl_encrypt($string, self::METHOD, $key, OPENSSL_RAW_DATA, $iv);

            // verify if we managed to encrypt the message
            if (!$cipherText) {
                return false; // don't throw the exception, so we don't expose the key
            }

            // concatenate and encode the result
            return base64_encode($iv . $cipherText);
        } catch (\Exception $e) {
            return false;
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
     * @return string Decrypted string.
     * @throws CryptException
     */
    public function decrypt($string, $key)
    {
        try {
            $string = base64_decode($string);
            if (!$string) {
                return false;
            }

            $key = hash(self::HASH, $key);
            $iv = $this->subStr($string, 0, 16);
            $cipherText = $this->subStr($string, 16);

            $msg = openssl_decrypt($cipherText, self::METHOD, $key, OPENSSL_RAW_DATA, $iv);

            return $msg;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Creates a hash from the given $password string.
     * The hashing algorithm used depends on your config.
     *
     * @param string $password String you wish to hash.
     *
     * @return string Hash of the given string.
     */
    public function createPasswordHash($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);

    }

    /**
     * Verify if the given $hash matches the given $password.
     *
     * @param string $password Original, un-hashed, password.
     * @param string $hash Hash string to which the check should be made
     *
     * @return bool True if $password matches the $hash, otherwise false is returned.
     */
    public function verifyPasswordHash($password, $hash)
    {
        return password_verify($password, $hash);
    }

    /**
     * Generates a random integer between the given $min and $max values.
     *
     * @param int $min Lower limit.
     * @param int $max Upper limit
     *
     * @return int Random number between $min and $max.
     */
    public function generateRandomInt($min = 1, $max = PHP_INT_MAX)
    {
        return random_int($min, $max);
    }

    /**
     * Generates a random string using the defined character set.
     * If $chars param is empty, the string will be generated using numbers, letters and special characters.
     *
     * @param int    $length Length of the generated string.
     * @param string $chars A string containing a list of chars that will be uses for generating the random string.
     *
     * @return string Random string with the given $length containing only the provided set of $chars.
     */
    public function generateRandomString($length, $chars = '')
    {
        // define the character map
        if (empty($chars)) {
            $chars = self::CHARS . self::NUMBERS . self::SYMBOLS;
        }

        $mapSize = strlen($chars);

        $string = '';
        for ($i = 0; $i < $length; ++$i) {
            $string .= $chars[($this->generateRandomInt(1, $mapSize)-1)];
        }
        return $string;
    }

    /**
     * Generates a random string, but without using special characters that are hard to read.
     * This method is ok to use for generating random user passwords. (which, of course, should be changed after first login).
     *
     * @param int $length Length of the random string.
     *
     * @return string Random string with the given $length.
     */
    public function generateUserReadableString($length)
    {
        return $this->generateRandomString($length, self::CHARS . self::NUMBERS);
    }

    /**
     * Generates a random string with a lot of 'noise' (special characters).
     * Use this method to generate API keys, salts and similar.
     *
     * @param int $length Length of the random string.
     *
     * @return string Random string with the given $length.
     */
    public function generateHardReadableString($length)
    {
        return $this->generateRandomString($length, self::SYMBOLS . self::CHARS . self::NUMBERS . self::SYMBOLS);
    }

    /**
     * Helper function for substr.
     *
     * @param $str
     * @param $start
     * @param $len
     *
     * @return string
     */
    private function subStr($str, $start, $len = null)
    {
        return mb_substr($str, $start, $len, '8bit');
    }
}
