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
        $encoder = new Encoder('\Webiny\Component\Security\Tests\Mocks\EncoderMock');
        $this->assertInstanceOf('\Webiny\Component\Security\Encoder\Encoder', $encoder);
    }

    /**
     * @expectedException \Webiny\Component\Security\Encoder\EncoderException
     */
    public function testConstructorException()
    {
        new Encoder('FakeDriver');
    }

    public function testCreatePasswordHash()
    {
        $encoder = new Encoder('\Webiny\Component\Security\Tests\Mocks\EncoderMock');
        $this->assertSame('password', $encoder->createPasswordHash('password')); // since we use the mock encoder, there is no hashing
    }

    public function testVerifyPasswordHashTrue()
    {
        $encoder = new Encoder('\Webiny\Component\Security\Tests\Mocks\EncoderMock');
        $this->assertTrue($encoder->verifyPasswordHash('password', 'password'));
    }

    public function testVerifyPasswordHashFalse()
    {
        $encoder = new Encoder('\Webiny\Component\Security\Tests\Mocks\EncoderMock');
        $this->assertFalse($encoder->verifyPasswordHash('admin', 'password'));
    }
}