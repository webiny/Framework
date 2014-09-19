<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Security\Tests\Encoder;

use Webiny\Component\Security\Encoder\Encoder;

class EncoderTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $encoder = new Encoder('\Webiny\Component\Security\Tests\Mocks\EncoderMock', 'Test');
        $this->assertInstanceOf('\Webiny\Component\Security\Encoder\Encoder', $encoder);
    }

    /**
     * @expectedException \Webiny\Component\Security\Encoder\EncoderException
     */
    public function testConstructorException()
    {
        new Encoder('FakeDriver', 'Test');
    }

    public function testCreatePasswordHash()
    {
        $encoder = new Encoder('\Webiny\Component\Security\Tests\Mocks\EncoderMock', 'Test');
        $this->assertSame('passwordTest', $encoder->createPasswordHash('password'));
    }

    public function testVerifyPasswordHashTrue()
    {
        $encoder = new Encoder('\Webiny\Component\Security\Tests\Mocks\EncoderMock', 'Test');
        $this->assertTrue($encoder->verifyPasswordHash('password', 'passwordTest'));
    }

    public function testVerifyPasswordHashFalse()
    {
        $encoder = new Encoder('\Webiny\Component\Security\Tests\Mocks\EncoderMock', 'Test');
        $this->assertFalse($encoder->verifyPasswordHash('admin', 'password'));
    }
}