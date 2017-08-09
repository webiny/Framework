<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Crypt\Tests;

use Webiny\Component\Crypt\Crypt;

class McryptTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        \Webiny\Component\Crypt\Bridge\Crypt::setLibrary('Webiny\Component\Crypt\Bridge\Mcrypt\Crypt');
    }

    public function testConstructor()
    {
        $crypt = new Crypt();

        $this->assertInstanceOf('\Webiny\Component\Crypt\Crypt', $crypt);
    }

    public function testGenerateRandomInt()
    {
        $crypt = new Crypt();
        $randomInt = $crypt->generateRandomInt(10, 20);

        $this->assertGreaterThanOrEqual(10, $randomInt);
    }

    public function testGenerateRandomInt2()
    {
        $crypt = new Crypt();
        $randomInt = $crypt->generateRandomInt(10, 20);

        $this->assertLessThanOrEqual(20, $randomInt);
    }

    public function testGenerateRandomInt3()
    {
        $crypt = new Crypt();
        $randomInt = $crypt->generateRandomInt(10, 10);

        $this->assertSame(10, $randomInt);
    }

    public function testGenerateRandomString()
    {
        $crypt = new Crypt();
        $randomString = $crypt->generateRandomString(9, 'abc');

        $this->assertInternalType('string', $randomString);
    }

    public function testGenerateUserReadableString()
    {
        $crypt = new Crypt();
        $randomString = $crypt->generateUserReadableString(64);

        $size = strlen($randomString);
        $this->assertSame(64, $size);
    }


    public function testGenerateHardReadableString()
    {
        $crypt = new Crypt();
        $randomString = $crypt->generateHardReadableString(64);

        $size = strlen($randomString);
        $this->assertSame(64, $size);
    }

    public function testCreatePasswordHash()
    {
        $crypt = new Crypt();
        $password = $crypt->createPasswordHash('login123');

        // $2y$ is the prefix for the default 'Blowfish' password algorithm
        $this->assertStringStartsWith('$2y$', $password);
    }

    public function testVerifyPasswordHash()
    {
        $crypt = new Crypt();
        $password = $crypt->createPasswordHash('login123');

        $this->assertTrue($crypt->verifyPasswordHash('login123', $password));
    }

    public function testVerifyPasswordHash2()
    {
        $crypt = new Crypt();
        $password = $crypt->createPasswordHash('login123');

        $this->assertFalse($crypt->verifyPasswordHash('123login', $password));
    }

    /**
     * @dataProvider encryptDescryptDataProvider
     */
    public function testEncryptDecrypt($stringToEncrypt, $encKey, $decKey, $result)
    {
        $crypt = new Crypt();
        $encrypted = $crypt->encrypt($stringToEncrypt, $encKey);

        $this->assertSame($result, $crypt->decrypt($encrypted, $decKey));
    }


    public function testEncryptDecryptFail()
    {
        $crypt = new Crypt();
        $encrypted = $crypt->encrypt('test string', 'some key');

        $result = $crypt->decrypt($encrypted, 'wrong key');
        $this->assertFalse($result);
    }

    public function encryptDescryptDataProvider()
    {
        return [
            // decryption matches the original string
            [
                'a',
                'abcdefgh12345678',
                'abcdefgh12345678',
                'a'
            ],
            [
                'B',
                '12345678abcdefgh',
                '12345678abcdefgh',
                'B'
            ],
            [
                'test string',
                'abcdefgh12345678',
                'abcdefgh12345678',
                'test string'
            ],
            [
                'test string',
                'abcdefgh/&%$#"!?',
                'abcdefgh/&%$#"!?',
                'test string'
            ],
            [
                'test string',
                '-.,_:;></&%$#"!?',
                '-.,_:;></&%$#"!?',
                'test string'
            ]
        ];
    }
}
