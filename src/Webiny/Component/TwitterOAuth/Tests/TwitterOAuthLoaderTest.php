<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\TwitterOAuth\Tests;

use Webiny\Component\TwitterOAuth\TwitterOAuth;
use Webiny\Component\TwitterOAuth\TwitterOAuthLoader;


class TwitterOAuthLoaderTest extends \PHPUnit_Framework_TestCase
{

    const CONFIG = '/ExampleConfig.yaml';

    public function testGetInstance()
    {
        TwitterOAuth::setConfig(realpath(__DIR__ . '/' . self::CONFIG));

        // we need to mock the $_SERVER
        $_SERVER = [
            'USER'            => 'webiny',
            'HOME'            => '/home/webiny',
            'SCRIPT_FILENAME' => '/var/www/projects/webiny/Public/index.php',
            'SCRIPT_NAME'     => '/index.php',
            'REQUEST_URI'     => '/batman-is-better-than-superman/?batman=one&superman=two',
            'DOCUMENT_URI'    => '/index.php',
            'SERVER_PROTOCOL' => 'HTTP/1.1',
            'REMOTE_ADDR'     => '192.168.58.1',
            'SERVER_NAME'     => 'admin.w3.com',
        ];

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