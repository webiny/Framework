<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Crypt\Bridge\Webiny;

use Webiny\Component\Crypt\Bridge\CryptInterface;
use Webiny\Component\Crypt\CryptException;

/**
 * Class Crypt.
 *
 * This is a simple class providing the basic cryptographic methods with a medium random string generator.
 *
 * @package Webiny\Component\Crypt\Bridge\Webiny
 */
class Crypt implements CryptInterface
{
    const CHARS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    const NUMBERS = '0123456789';
    const SYMBOLS = '!"#$%&\'()* +,-./:;<=>?@[\]^_`{|}~';

    /**
     * @var string Name of the password algorithm.
     */
    private $_passwordAlgo;

    /**
     * @var string Cipher block.
     */
    private $_cipherBlock;

    /**
     * @var string Cipher mode.
     */
    private $_cipherMode;


    /**
     * Base constructor
     *
     * @param string $passwordAlgo Password hashing algorithm.
     * @param string $cipherMode   Cipher mode.
     * @param string $cipherBlock  Cipher block size.
     */
    function __construct($passwordAlgo, $cipherMode, $cipherBlock)
    {
        $this->_passwordAlgo = $passwordAlgo;
        $this->_cipherMode = $cipherMode;
        $this->_cipherBlock = $cipherBlock;
    }

    /**
     * Generates a random integer between the given $min and $max values.
     *
     * @param int $min Lower limit.
     * @param int $max Upper limit
     *
     * @return int Random number between $min and $max.
     */
    function generateRandomInt($min = 1, $max = PHP_INT_MAX)
    {
        // get the range
        $range = $max - $min;
        if ($range === 0) {
            return $min;
        }

        // how many bytes we need to generate, based on the range
        $bytes = max(floor(mb_strlen($range, '8bit') / 8), 1);

        // let's generate a random number that is greater than the $range
        $num = 1;
        do {
            $temp = $num * hexdec(bin2hex($this->_generator($bytes)));
            if ($temp > 0) {
                if ($temp < PHP_INT_MAX) {
                    $num = $temp;
                } else {
                    break;
                }
            }
        } while ($num < $range);

        if ($num > $range) {
            // correct the $int, to fall within a random place between 0 and &range
            $minTemp = $num - $range;
            $randDiff = mt_rand($minTemp, $num);
            $num -= $randDiff;
            $num += $min;
        } else {
            if ($num <= $range) {
                // add random number to $min
                // happens in case when we had to break the loop, because $int would be greater than PHP_INT_MAX
                $num += $min;
            }
        }

        return $num;
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
    function generateRandomString($length, $chars = '')
    {
        // generate a random string
        $random = $this->_generator($length);

        // define the character map
        if ($chars == '') {
            $chars = self::CHARS . self::NUMBERS . self::SYMBOLS;
        }

        $len = strlen($chars);

        // build a string by converting the generated random string
        // into a random number that is placed within the defined character map
        $string = '';
        for ($i = 0; $i < $length; $i++) {
            $o = ord($random[$i]) % $len;
            $string .= $chars[$o];
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
    function generateUserReadableString($length)
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
    function generateHardReadableString($length)
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
    function createPasswordHash($password)
    {
        return password_hash($password, $this->_passwordAlgo);
    }

    /**
     * Verify if the given $hash matches the given $password.
     *
     * @param string $password Original, un-hashed, password.
     * @param string $hash     Hash string to which the check should be made
     *
     * @return bool True if $password matches the $hash, otherwise false is returned.
     */
    function verifyPasswordHash($password, $hash)
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
    function encrypt($string, $key)
    {
        // hash the key, so we have the required key size
        $key = $this->_getKeyHash($key);

        // create initialization vector
        $ivSize = mcrypt_get_iv_size($this->_cipherBlock, $this->_cipherMode);
        if (!$ivSize) {
            throw new CryptException('The provided cipher doesn\'t support the block size.');
        }
        $iv = $this->generateRandomString($ivSize);

        // used later to verify if the decrypted string is correct (use a weaker hash to save space)
        $stringHash = hash('crc32', $string);

        // encrypt the message
        $cipherText = mcrypt_encrypt($this->_cipherBlock, $key, $stringHash . $string, $this->_cipherMode, $iv);

        // verify if we managed to encrypt the message
        if (!$cipherText) {
            throw new CryptException('Unable to encrypt the data.
            Check that the provided key has the proper length.'
            );
        }

        // concatenate and encode the result
        return base64_encode($iv . $cipherText);
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
    function decrypt($string, $key)
    {
        // hash the key
        $key = $this->_getKeyHash($key);

        // decode the string
        $text = base64_decode($string);

        // we need to know the IV size
        $ivSize = mcrypt_get_iv_size($this->_cipherBlock, $this->_cipherMode);
        if (!$ivSize) {
            throw new CryptException('The provided cipher doesn\'t support the block size.');
        }

        // extract the iv from the message
        $iv = substr($text, 0, $ivSize);

        // remove the iv from the message
        $text = substr($text, $ivSize);

        // decrypt the message
        $plainText = mcrypt_decrypt($this->_cipherBlock, $key, $text, $this->_cipherMode, $iv);

        // extract the  string hash from the message
        $stringHash = substr($plainText, 0, 8);
        $plainText = substr($plainText, 8);

        // verify if the message was decrypted correctly
        if (!$plainText || hash('crc32', trim($plainText)) != $stringHash) {
            throw new CryptException('Unable to decrypt the data.');
        }

        // return the result
        return trim($plainText);
    }

    /**
     * A simple seed generator that uses a combination of mcrypt_create_iv (MCRYPT_DEV_URANDOM), mt_rand and microtime.
     * The combination of those three factors should generate a medium strength random string.
     *
     * @param int $size Size of the generated string
     *
     * @return string
     */
    private function _generator($size)
    {
        // seed: mcrypt
        $seedOne = mcrypt_create_iv($size, MCRYPT_DEV_URANDOM);

        // seed: mt_rand
        $seedTwo = '';
        for ($i = 0; $i < $size; $i++) {
            $seedTwo .= chr((mt_rand() ^ (mt_rand() / getrandmax())) % 256);
        }

        // seed: microtime
        $seedThree = '';
        do {
            $seedThree .= hash('sha256', microtime(true));
        } while (strlen($seedThree) < $size);

        // mix the seeds
        $random = str_shuffle($seedThree . $seedOne . $seedTwo);

        return substr($random, 0, $size);
    }

    /**
     * Generates a hash from the given key. The has length is determined by the cipher mode and cipher block.
     *
     * @param string $key Key for which the hash should be generated.
     *
     * @return string
     */
    private function _getKeyHash($key)
    {
        // get key size based on the block and mode
        $keySize = mcrypt_get_key_size($this->_cipherBlock, $this->_cipherMode);

        // generate and return the key hash
        $key = hash('sha256', $key, true);
        return substr($key, 0, $keySize);
    }
}