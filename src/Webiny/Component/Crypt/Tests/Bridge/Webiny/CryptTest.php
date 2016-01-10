<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Crypt\Tests\Bridge\Webiny;

use \Webiny\Component\Crypt\Bridge\Webiny\Crypt as CryptBridge;

class CryptTest extends \PHPUnit_Framework_TestCase
{

    // test number generator

    public function randomIntProvider()
    {
        return [
            [0, 0],
            [1, 1],
            [0, 1],
            [0, 1000],
            [1000, 1000],
            [0, PHP_INT_MAX],
            [PHP_INT_MAX - 100, PHP_INT_MAX]
        ];
    }

    /**
     * @dataProvider randomIntProvider
     */
    public function testGenerateRandomInt($min, $max)
    {
        $c = new CryptBridge(CRYPT_BLOWFISH, MCRYPT_MODE_ECB, MCRYPT_RIJNDAEL_256);
        $int = $c->GenerateRandomInt($min, $max);
        $this->assertGreaterThanOrEqual($min, $int);
        $this->assertLessThanOrEqual($max, $int);
    }

    public function testGenerateRandomIntRandomness()
    {
        $c = new CryptBridge(CRYPT_BLOWFISH, MCRYPT_MODE_ECB, MCRYPT_RIJNDAEL_256);

        $genNumbers = [];
        for ($i = 0; $i < 100; $i++) {
            $genNumbers[] = $c->GenerateRandomInt(0, PHP_INT_MAX);
        }

        // we expect to have at least 95% of randomness
        $randomness = count(array_unique($genNumbers));

        $this->assertGreaterThanOrEqual(95, $randomness);
    }

    // test the string generator

    public function randomStringProvider()
    {
        return [
            [1, 'a'],
            [10, 'bcd'],
            [100, CryptBridge::CHARS],
            [100, CryptBridge::NUMBERS],
            [200, CryptBridge::SYMBOLS],
            [300, CryptBridge::SYMBOLS . CryptBridge::NUMBERS . CryptBridge::CHARS],
        ];
    }

    /**
     * Test the basic character set.
     * @dataProvider randomStringProvider
     */
    public function testGenerateRandomString($strLen, $charSet)
    {
        $c = new CryptBridge(CRYPT_BLOWFISH, MCRYPT_MODE_ECB, MCRYPT_RIJNDAEL_256);
        $str = $c->generateRandomString($strLen, $charSet);

        $len = strlen($str);
        $this->assertSame($strLen, $len);

        $chars = array_unique(str_split($str));
        $diff = array_diff($chars, str_split($charSet));
        $this->assertSame(0, count($diff));
    }

    public function testGenerateUserReadableString()
    {
        $c = new CryptBridge(CRYPT_BLOWFISH, MCRYPT_MODE_ECB, MCRYPT_RIJNDAEL_256);
        $str = $c->generateUserReadableString(100);

        $len = strlen($str);
        $this->assertSame(100, $len);

        $chars = array_unique(str_split($str));
        $diff = array_diff($chars, str_split(CryptBridge::NUMBERS . CryptBridge::CHARS));
        $this->assertSame(0, count($diff));
    }

    public function testGenerateHardReadableString()
    {
        $c = new CryptBridge(CRYPT_BLOWFISH, MCRYPT_MODE_ECB, MCRYPT_RIJNDAEL_256);
        $str = $c->generateHardReadableString(100);

        $len = strlen($str);
        $this->assertSame(100, $len);

        $chars = array_unique(str_split($str));
        $diff = array_diff($chars, str_split(CryptBridge::SYMBOLS . CryptBridge::NUMBERS . CryptBridge::CHARS));
        $this->assertSame(0, count($diff));
    }

    // test password hashes
    public function passwordProvider()
    {
        return [
            ['a', CRYPT_BLOWFISH, 60],
            ['secret', CRYPT_BLOWFISH, 60],
            ['aVeryLongSecretWithNumbersAndSymbols123!@#!#${}"|', CRYPT_BLOWFISH, 60],
        ];
    }

    /**
     * @dataProvider passwordProvider
     */
    public function testPasswordMethods($password, $algo, $passLen)
    {
        $c = new CryptBridge($algo, MCRYPT_MODE_ECB, MCRYPT_RIJNDAEL_256);

        $passwordHash = $c->createPasswordHash($password);
        $this->assertNotSame($password, $passwordHash);

        $result = $c->verifyPasswordHash($password, $passwordHash);
        $this->assertTrue($result);

        $this->assertSame($passLen, strlen($passwordHash));
    }

    // test encrypt decrypt
    public function encryptDecryptProvider()
    {
        $ciphers = [
            MCRYPT_3DES,
            MCRYPT_DES,
            MCRYPT_RIJNDAEL_128,
            MCRYPT_RIJNDAEL_192,
            MCRYPT_RIJNDAEL_256,
            MCRYPT_BLOWFISH,
            MCRYPT_CAST_128,
            MCRYPT_CAST_256
        ];

        $blocks = [
            MCRYPT_MODE_ECB,
            MCRYPT_MODE_CBC,
            MCRYPT_MODE_CFB,
            MCRYPT_MODE_OFB,
            MCRYPT_MODE_NOFB
        ];

        $return = [];
        foreach ($ciphers as $c) {
            foreach ($blocks as $b) {
                $return[] = ['some text', 'secret key', $b, $c];
            }
        }

        return $return;
    }

    /**
     * @dataProvider encryptDecryptProvider
     */
    public function testEncryptDecrypt($text, $key, $mode, $cipher)
    {
        $c = new CryptBridge(CRYPT_BLOWFISH, $mode, $cipher);

        $encText = $c->encrypt($text, $key);
        $this->assertNotSame($encText, $text);

        $decText = $c->decrypt($encText, $key);
        $this->assertSame($text, $decText);
    }


    public function testEncryptDecryptException()
    {
        $c = new CryptBridge(CRYPT_BLOWFISH, MCRYPT_MODE_ECB, MCRYPT_RIJNDAEL_256);
        $data = $c->encrypt('test', 'secret key');
        $result = $c->decrypt($data, 'wrong key');

        $this->assertFalse($result);
    }
}
