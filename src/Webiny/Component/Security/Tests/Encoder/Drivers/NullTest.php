<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Security\Tests\Encoder\Drivers;

use Webiny\Component\Security\Encoder\Drivers\Null;

class NullTest extends \PHPUnit_Framework_TestCase
{

    public function testCreatePasswordHash()
    {
        $driver = new Null();
        $this->assertSame('password', $driver->createPasswordHash('password'));
    }

    public function testVerifyPasswordHashTrue()
    {
        $driver = new Null();
        $this->assertTrue($driver->verifyPasswordHash('password', 'password'));
    }

    public function testVerifyPasswordHashFalse()
    {
        $driver = new Null();
        $this->assertFalse($driver->verifyPasswordHash('admin', 'password'));
    }
}