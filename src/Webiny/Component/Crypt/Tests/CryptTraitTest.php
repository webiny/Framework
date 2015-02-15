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
    }

}