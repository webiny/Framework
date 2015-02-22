<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Security\Token\CryptDrivers;

/**
 * CryptInterface must be implemented by every token crypt driver.
 *
 * @package         Webiny\Component\Security\Token\Crypt
 */
interface CryptDriverInterface
{
    /**
     * Encrypts the given $string using the $key variable as encryption key.
     *
     * @param string $string Raw string that should be encrypted.
     * @param string $key    Security key used for encryption.
     *
     * @return string Encrypted key.
     */
    public function encrypt($string, $key);

    /**
     * Decrypts the given $string that was encrypted with the $key.
     *
     * @param string $string Encrypted string.
     * @param string $key    Key used for encryption.
     *
     * @return string Decrypted string.
     */
    public function decrypt($string, $key);

}