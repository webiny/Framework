<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Crypt\Tests;

use Webiny\Component\Crypt\Crypt;

class CryptTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Crypt::setConfig(__DIR__ . '/ExampleConfig.yaml');
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
        $randomString = $crypt->generateRandomString(9, $chars = 'abc');

        $this->assertInternalType('string', $randomString);
    }

    public function testGenerateUserReadableString()
    {
        $crypt = new Crypt();
        $randomString = $crypt->generateUserReadableString(64);

        $size = strlen($randomString);
        $this->assertSame(64, $size);
    }

    public function testGenerateUserReadableString2()
    {
        $crypt = new Crypt();
        $randomString = $crypt->generateUserReadableString('asd');

        $size = strlen($randomString);
        $this->assertSame(0, $size);
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
     * @expectedException \Webiny\Component\Crypt\CryptException
     * @expectedExceptionMessage The supplied key block is in the valid sizes
     */
    public function testEncrypt()
    {
        $crypt = new Crypt();
        $crypt->encrypt('some string', 'too short key');
    }

    /**
     * @expectedException \Webiny\Component\Crypt\CryptException
     * @expectedExceptionMessage Supplied Initialization Vector is too short
     */
    public function testEncrypt2()
    {
        $crypt = new Crypt();
        $crypt->encrypt('some string', 'abcdefgh12345678', 'too short');
    }

    /**
     * @dataProvider encryptDescryptDataProvider
     */
    public function testEncryptDecrypt($stringToEncrypt, $encKey, $encInitValue, $decKey, $decInitValue, $result)
    {
        $crypt = new Crypt();
        $encrypted = $crypt->encrypt($stringToEncrypt, $encKey, $encInitValue);

        $this->assertSame($result, $crypt->decrypt($encrypted, $decKey, $decInitValue));
    }

    public function encryptDescryptDataProvider()
    {
        return [
            // decryption matches the original string
            [
                'a',
                'abcdefgh12345678',
                'init_vector',
                'abcdefgh12345678',
                'init_vector',
                'a'
            ],
            [
                'B',
                '12345678abcdefgh',
                'init_vector',
                '12345678abcdefgh',
                'init_vector',
                'B'
            ],
            [
                'test string',
                'abcdefgh12345678',
                'init_vector',
                'abcdefgh12345678',
                'init_vector',
                'test string'
            ],
            [
                'test string',
                'abcdefgh/&%$#"!?',
                'init_vector',
                'abcdefgh/&%$#"!?',
                'init_vector',
                'test string'
            ],
            [
                'test string',
                '-.,_:;></&%$#"!?',
                'init_vector',
                '-.,_:;></&%$#"!?',
                'init_vector',
                'test string'
            ],
            // decryption fails because the key doesn't match
            [
                'test string',
                'abcdefgh12345678',
                'init_vector',
                '12345678abcdefgh',
                'init_vector',
                false
            ],
            // decryption fails because the initialization vector doesn't match
            [
                'test string',
                'abcdefgh12345678',
                'init_vector',
                'abcdefgh12345678',
                'foo_vector_',
                false
            ],
        ];
    }
}