<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Crypt\Tests;

use Webiny\Component\Crypt\Crypt;
use Webiny\Component\Crypt\CryptTrait;

class CryptTraitTest extends \PHPUnit_Framework_TestCase
{
    use CryptTrait;

    public function setUp()
    {
        Crypt::setConfig(__DIR__ . '/ExampleConfig.yaml');
    }

    public function testCrypt()
    {
        $instance = $this->crypt('Password');
        $this->assertInstanceOf('\Webiny\Component\Crypt\Crypt', $instance);

        $string = "some test string";
        $encString = $instance->encrypt($string, "test_key_secret_");
        $this->assertNotSame($string, $encString);

        $this->assertNotSame($string, $instance->decrypt($encString, "test_key_secret2"));
        $this->assertSame($string, $instance->decrypt($encString, "test_key_secret_"));
    }

}