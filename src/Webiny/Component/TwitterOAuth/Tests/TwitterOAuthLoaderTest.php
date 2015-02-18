<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\TwitterOAuth\Tests;

use Webiny\Component\Http\Request;
use Webiny\Component\TwitterOAuth\TwitterOAuth;
use Webiny\Component\TwitterOAuth\TwitterOAuthLoader;
use Webiny\Component\TwitterOAuth\Bridge\TwitterOAuth as Bridge;


class TwitterOAuthLoaderTest extends \PHPUnit_Framework_TestCase
{

    public function testGetInstance()
    {
        TwitterOAuth::setConfig(realpath(__DIR__ . '/ExampleConfig.yaml'));

        Request::getInstance()->setCurrentUrl('http://admin.w3.com/batman-is-better-than-superman/?batman=one&superman=two');

        // other tests might change the library, which can cause this test to fail
        Bridge::setLibrary('\Webiny\Component\TwitterOAuth\Bridge\League\TwitterOAuth');

        $instance = TwitterOAuthLoader::getInstance('MyTwitterApp');
        $this->assertInstanceOf('\Webiny\Component\TwitterOAuth\TwitterOAuth', $instance);
    }

    /**
     * @expectedException \Webiny\Component\TwitterOAuth\TwitterOAuthException
     * @expectedExceptionMessage Unable to read "TwitterOAuth.doesnt exist" configuration.
     */
    public function testGetInstanceException()
    {
        TwitterOAuthLoader::getInstance('doesnt exist');
    }
}