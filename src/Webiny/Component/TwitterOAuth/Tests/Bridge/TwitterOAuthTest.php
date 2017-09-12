<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\TwitterOAuth\Tests\Bridge;

use Webiny\Component\TwitterOAuth\Bridge\TwitterOAuth;
use Webiny\Component\TwitterOAuth\Bridge\TwitterOAuthInterface;
use Webiny\Component\TwitterOAuth\Tests\Mocks\FakeBridgeMock;

class TwitterOAuthTest extends \PHPUnit_Framework_TestCase
{
    public function testGetInstance()
    {
        $this->assertInstanceOf(TwitterOAuthInterface::class, TwitterOAuth::getInstance('client', 'secret', 'redirect'));
    }

    /**
     * @expectedException \Webiny\Component\TwitterOAuth\Bridge\TwitterOAuthException
     */
    public function testGetInstanceException()
    {
        TwitterOAuth::setLibrary(FakeBridgeMock::class);
        TwitterOAuth::getInstance('client', 'secret', 'redirect');
    }
}