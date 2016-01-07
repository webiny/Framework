<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Security\Tests\Encoder\Drivers;

use Webiny\Component\Security\Encoder\Drivers\Plain;

class PlainTest extends \PHPUnit_Framework_TestCase
{

    public function testCreatePasswordHash()
    {
        $driver = new Plain();
        $this->assertSame('password', $driver->createPasswordHash('password'));
    }

    public function testVerifyPasswordHashTrue()
    {
        $driver = new Plain();
        $this->assertTrue($driver->verifyPasswordHash('password', 'password'));
    }

    public function testVerifyPasswordHashFalse()
    {
        $driver = new Plain();
        $this->assertFalse($driver->verifyPasswordHash('admin', 'password'));
    }
}