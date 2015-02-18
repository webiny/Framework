<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\TwitterOAuth\Tests\Bridge;

use Webiny\Component\TwitterOAuth\Bridge\TwitterOAuth;

class TwitterOAuthTest extends \PHPUnit_Framework_TestCase
{
    public function testGetInstance()
    {
        $this->assertInstanceOf('\Webiny\Component\TwitterOAuth\Bridge\TwitterOAuthInterface',
                                TwitterOAuth::getInstance('client', 'secret', 'redirect')
        );
    }

    /**
     * @expectedException \Webiny\Component\TwitterOAuth\Bridge\TwitterOAuthException
     */
    public function testGetInstanceException()
    {
        TwitterOAuth::setLibrary('\Webiny\Component\TwitterOAuth\Tests\Mocks\FakeBridgeMock');
        TwitterOAuth::getInstance('client', 'secret', 'redirect');
    }
}