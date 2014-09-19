<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Security\Tests\Mocks;

use Webiny\Component\Config\ConfigObject;
use Webiny\Component\Security\Token\CryptDrivers\CryptDriverInterface;

/**
 * Token crypt driver mock
 *
 * @package         Webiny\Component\Security\Tests\Mocks
 */
class TokenCryptDriverMock implements CryptDriverInterface
{

    /**
     * Creates an new crypt driver instance.
     *
     * @param ConfigObject $config ConfigObject instance containing all the parameters defined under the driver.
     *
     * @return \Webiny\Component\Security\Tests\Mocks\TokenCryptDriverMock
     */
    public function __construct()
    {

    }

    /**
     * Encrypts the given $string using the $key variable as encryption key.
     *
     * @param string $string Raw string that should be encrypted.
     * @param string $key    Security key used for encryption.
     *
     * @return string Encrypted key.
     */
    public function encrypt($string, $key)
    {
        return $string;
    }

    /**
     * Decrypts the given $string that was encrypted with the $key.
     *
     * @param string $string Encrypted string.
     * @param string $key    Key used for encryption.
     *
     * @return string Decrypted string.
     */
    public function decrypt($string, $key)
    {
        return $string;
    }
}