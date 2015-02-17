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


class TwitterOAuthLoaderTest extends \PHPUnit_Framework_TestCase
{

    const CONFIG = '/ExampleConfig.yaml';

    public function testGetInstance()
    {
        TwitterOAuth::setConfig(realpath(__DIR__ . '/' . self::CONFIG));

        Request::getInstance()->setCurrentUrl('http://admin.w3.com/batman-is-better-than-superman/?batman=one&superman=two');

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