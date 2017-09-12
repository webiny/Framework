<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Security\Tests\Encoder;

use Webiny\Component\Security\Encoder\Encoder;
use Webiny\Component\Security\Tests\Mocks\EncoderMock;

class EncoderTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $encoder = new Encoder(EncoderMock::class);
        $this->assertInstanceOf(Encoder::class, $encoder);
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
        $encoder = new Encoder(EncoderMock::class);
        $this->assertSame('password', $encoder->createPasswordHash('password')); // since we use the mock encoder, there is no hashing
    }

    public function testVerifyPasswordHashTrue()
    {
        $encoder = new Encoder(EncoderMock::class);
        $this->assertTrue($encoder->verifyPasswordHash('password', 'password'));
    }

    public function testVerifyPasswordHashFalse()
    {
        $encoder = new Encoder(EncoderMock::class);
        $this->assertFalse($encoder->verifyPasswordHash('admin', 'password'));
    }
}