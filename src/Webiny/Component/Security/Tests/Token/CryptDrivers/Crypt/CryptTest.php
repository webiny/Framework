<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Security\Tests\Token\CryptDrivers\Crypt;

use Webiny\Component\Config\ConfigObject;
use Webiny\Component\Security\Token\CryptDrivers\Crypt\Crypt;

class CryptTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        \Webiny\Component\Crypt\Crypt::setConfig(__DIR__ . '/CryptExampleConfig.yaml');
    }

    public function testConstructor()
    {
        $crypt = new Crypt('Password');
        $this->assertInstanceOf('\Webiny\Component\Security\Token\CryptDrivers\Crypt\Crypt', $crypt);
    }

    public function testEncryptDecrypt()
    {
        $crypt = new Crypt('Password');

        $encrypted = $crypt->encrypt('password', 'someSecuredKey12');

        $this->assertNotSame('password', $encrypted);

        $decrypted = $crypt->decrypt($encrypted, 'someSecuredKey12');

        $this->assertSame('password', $decrypted);
    }
}