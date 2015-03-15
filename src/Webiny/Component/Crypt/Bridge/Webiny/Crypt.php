<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Crypt\Bridge\Webiny;

use Webiny\Component\Crypt\Bridge\CryptInterface;

/**
 * Class Crypt.
 *
 * This is a simple class providing the basic cryptographic methods.
 *
 * The class uses a combination of three different seeds for providing randomness:
 *  - MCRYPT_DEV_URANDOM,
 *  - mt_rand
 *  - microtime
 *
 * For mixing seeds we use a basic combination of mt_rand, shuffle and str_shuffle
 *
 * Password hashing and validation if done using nativ password_hash and password_verify methods.
 *
 * Encoding and decoding is done using mcrypt methods.
 *
 * Notice:
 * This class will provide the neccessary security for most your day-to-day operations, like
 * storing and verifying passwords, generating medium strenght random numbers and strings,
 * and also basic medium encryption and decryption.
 *
 * The library has been tested, but not reviewd by a security expert. If you have
 * any suggestions or improvements to report, feel free to open an issue.
 *
 * If you require a more advanced random library, with higher strenght random generator,
 * we suggest you use https://github.com/ircmaxell/RandomLib.
 *
 *
 * @package Webiny\Component\Crypt\Bridge\Webiny
 */
class Crypt implements CryptInterface
{
    const CHARS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    const NUMBERS = '0123456789';
    const SYMBOLS = '!"#$%&\'()* +,-./:;<=>?@[\]^_`{|}~';
    const HASH = 'sha512';

    /**
     * @var string Name of the password algorithm.
     */
    private $passwordAlgo;

    /**
     * @var string Cipher block.
     */
    private $cipherBlock;

    /**
     * @var string Cipher mode.
     */
    private $cipherMode;


    /**
     * Base constructor
     *
     * @param string $passwordAlgo Password hashing algorithm.
     * @param string $cipherMode   Cipher mode.
     * @param string $cipherBlock  Cipher block size.
     */
    public function __construct($passwordAlgo, $cipherMode, $cipherBlock)
    {
        $this->passwordAlgo = $passwordAlgo;
        $this->cipherMode = $cipherMode;
        $this->cipherBlock = $cipherBlock;
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
        // get the range
        $range = $max - $min;
        if ($range === 0) {
            return $min;
        }

        // explode the range
        $rangeData = str_split($range);

        // generate the random number within the range
        $num = '';
        foreach ($rangeData as $r) {
            // create char range
            $chars = '';
            for ($i = 0; $i <= $r; $i++) {
                $chars .= $i;
            }

            // generate a random int from the given chars
            $num .= $this->generateRandomString(1, $chars);
        }

        // cast to int
        $num = (int)$num;

        // add the random range number to min
        return $num + $min;
    }

    /**
     * Generates a random string using the defined character set.
     * If $chars param is empty, the string will be generated using numbers, letters and special characters.
     *
     * @param int    $length Length of the generated string.
     * @param string $chars  A string containing a list of chars that will be uses for generating the random string.
     *
     * @return string Random string with the given $length containing only the provided set of $chars.
     */
    public function generateRandomString($length, $chars = '')
    {
        // generate a random string
        $random = $this->generator($length);

        // define the character map
        if ($chars == '') {
            $chars = self::CHARS . self::NUMBERS . self::SYMBOLS;
        }

        $len = $this->strLen($chars);
        $mask = 256 - (256 % $len);

        // build a string by converting the generated random string
        // into a random number that is placed within the defined character map
        $string = '';
        for ($i = 0; $i < $length; $i++) {
            // is the current char within the mask range
            if (ord($random[$i]) >= $mask) {
                continue;
            }

            $o = ord($random[$i]) % $len;
            $string .= $chars[$o];
        }

        // check if we under-generated
        if ($this->strLen($string) < $length) {
            $string .= $this->generateRandomString(($length - $this->strLen($string)), $chars);
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
     * Creates a hash from the given $password string.
     * The hashing algorithm used depends on your config.
     *
     * @param string $password String you wish to hash.
     *
     * @return string Hash of the given string.
     */
    public function createPasswordHash($password)
    {
        return password_hash($password, $this->passwordAlgo);
    }

    /**
     * Verify if the given $hash matches the given $password.
     *
     * @param string $password Original, un-hashed, password.
     * @param string $hash     Hash string to which the check should be made
     *
     * @return bool True if $password matches the $hash, otherwise false is returned.
     */
    public function verifyPasswordHash($password, $hash)
    {
        return password_verify($password, $hash);
    }

    /**
     * Encrypt the given $string using a cypher and the secret $key
     *
     * @param string $string The string you want to encrypt.
     * @param string $key    The secret key that will be used to encrypt the string.
     *
     * @return string Encrypted string.
     * @throws CryptException
     */
    public function encrypt($string, $key)
    {
        // hash the key, so we have the required key size
        $key = $this->getKeyHash($key);

        // mac key
        $macKey = $this->hkdf($key, self::HASH);

        // create initialization vector
        $ivSize = mcrypt_get_iv_size($this->cipherBlock, $this->cipherMode);
        if (!$ivSize) {
            return false; // don't throw the exception, so we don't expose the key
        }
        $iv = $this->generateRandomString($ivSize);

        // encrypt the message
        $cipherText = mcrypt_encrypt($this->cipherBlock, $key, $string, $this->cipherMode, $iv);

        // verify if we managed to encrypt the message
        if (!$cipherText) {
            return false; // don't throw the exception, so we don't expose the key
        }

        // used later to verify if the decrypted string is correct
        $stringHash = hash_hmac(self::HASH, $cipherText . $iv, $macKey, true);

        // concatenate and encode the result
        return base64_encode($iv . $cipherText . $stringHash);
    }

    /**
     * Decrypt a string that has been encrypted with the 'encrypt' method.
     * In order to decrypt the string correctly, you must provide the same secret key that was used for the encryption
     * process.
     *
     * @param string $string The string you want to decrypt.
     * @param string $key    The secret key that was used to encrypt the $string.
     *
     * @return string Decrypted string.
     * @throws CryptException
     */
    public function decrypt($string, $key)
    {
        // hash the key
        $key = $this->getKeyHash($key);

        // mac key
        $macKey = $this->hkdf($key, self::HASH);

        // decode the string
        $string = base64_decode($string);
        if (!$string) {
            return false;
        }

        // we need to know the IV size
        $ivSize = mcrypt_get_iv_size($this->cipherBlock, $this->cipherMode);
        if (!$ivSize) {
            return false; // don't throw the exception, so we don't expose the key
        }

        // extract the iv from the message
        $iv = $this->subStr($string, 0, $ivSize);
        if ($this->strLen($iv) != $ivSize) {
            return false;
        }

        // extract the string hash from the message
        $stringHash = $this->subStr($string, -64);
        if ($this->strLen($stringHash) != 64) {
            return false;
        }

        // extract cipher text
        $cipherText = $this->subStr($string, $ivSize, -64);
        if ($this->strLen($string) <= ($ivSize + 64)) {
            return false;
        }

        // generate new hash
        $newStringHash = hash_hmac(self::HASH, $cipherText . $iv, $macKey, true);

        if ($newStringHash != $stringHash) {
            return false; // don't throw the exception, so we don't expose the key
        }

        // decrypt the message
        $plainText = mcrypt_decrypt($this->cipherBlock, $key, $cipherText, $this->cipherMode, $iv);

        // verify if the message was decrypted correctly
        if (!$plainText) {
            return false; // don't throw the exception, so we don't expose the key
        }

        // remove padding and return the result
        return rtrim($plainText, "\0");
    }

    /**
     * A simple seed generator that uses mcrypt_create_iv (MCRYPT_DEV_URANDOM).
     *
     * @param int $size Size of the generated string
     *
     * @return string
     */
    private function generator($size)
    {
        // seed: mcrypt
        return mcrypt_create_iv($size, MCRYPT_DEV_URANDOM);
    }

    /**
     * Generates a hash from the given key. The has length is determined by the cipher mode and cipher block.
     *
     * @param string $key Key for which the hash should be generated.
     *
     * @return string
     */
    private function getKeyHash($key)
    {
        // get key size based on the block and mode
        $keySize = mcrypt_get_key_size($this->cipherBlock, $this->cipherMode);

        // generate and return the key hash
        $key = hash(self::HASH, $key, true);

        return $this->subStr($key, 0, $keySize);
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

    private function strLen($str)
    {
        return mb_strlen($str, '8bit');
    }

    /**
     * HKDF
     * https://gist.github.com/narfbg/8793435
     *
     * @link    https://tools.ietf.org/rfc/rfc5869.txt
     *
     * @param                 $key       Input key
     * @param string          $digest    A SHA-2 hashing algorithm
     * @param                 $salt      Optional salt
     * @param                 $length    Output length (defaults to the selected digest size)
     * @param string          $info      Optional context/application-specific info
     *
     * @return string A pseudo-random key
     */
    private function hkdf($key, $digest = 'sha512', $salt = null, $length = null, $info = '')
    {
        if (!in_array($digest, array(
            'sha224',
            'sha256',
            'sha384',
            'sha512'
        ), true
        )
        ) {
            return false;
        }

        $digest_length = substr($digest, 3) / 8;
        if (empty($length) OR !is_int($length)) {
            $length = $digest_length;
        } elseif ($length > (255 * $digest_length)) {
            return false;
        }

        isset($salt) OR $salt = str_repeat("\0", substr($digest, 3) / 8);

        $prk = hash_hmac($digest, $key, $salt, true);
        $key = '';
        for ($key_block = '', $block_index = 1; strlen($key) < $length; $block_index++) {
            $key_block = hash_hmac($digest, $key_block . $info . chr($block_index), $prk, true);
            $key .= $key_block;
        }

        return substr($key, 0, $length);
    }
}
