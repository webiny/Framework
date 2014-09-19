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

    /**
     * @expectedException \Webiny\Component\Security\Token\CryptDrivers\Crypt\CryptException
     * @expectedExceptionMessage Service "Crypt.Fake" is not defined in services configuration file.
     */
    public function testConstructorException()
    {
        new Crypt('Fake');
    }

    public function testEncrypt()
    {
        $crypt = new Crypt('Password');
        $controlResult = "6SzWFRKj0AfVabH3zU1FNA==";
        $this->assertSame($controlResult, base64_encode($crypt->encrypt('password', 'someSecuredKey12')));
    }

    public function testDecrypt()
    {
        $crypt = new Crypt('Password');
        $encodedString = "6SzWFRKj0AfVabH3zU1FNA==";
        $this->assertSame('password', $crypt->decrypt(base64_decode($encodedString), 'someSecuredKey12'));
    }
}