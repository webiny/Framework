<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\OAuth2\Tests;

use Webiny\Component\OAuth2\OAuth2;
use Webiny\Component\OAuth2\OAuth2Loader;


class OAuth2LoaderTest extends \PHPUnit_Framework_TestCase
{

    const CONFIG = '/ExampleConfig.yaml';

    public function testGetInstance()
    {
        OAuth2::setConfig(realpath(__DIR__ . '/' . self::CONFIG));

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

        $instance = OAuth2Loader::getInstance('Facebook');
        $this->assertInstanceOf('\Webiny\Component\OAuth2\OAuth2', $instance);
    }

    /**
     * @expectedException \Webiny\Component\OAuth2\OAuth2Exception
     * @expectedExceptionMessage Unable to read "OAuth2.doesnt exist" configuration.
     */
    public function testGetInstanceException()
    {
        OAuth2Loader::getInstance('doesnt exist');
    }

    /**
     * @expectedException \Webiny\Component\OAuth2\OAuth2Exception
     * @expectedExceptionMessage Server missing
     */
    public function testGetInstanceServerException()
    {
        OAuth2Loader::getInstance('GPlus');
    }
}