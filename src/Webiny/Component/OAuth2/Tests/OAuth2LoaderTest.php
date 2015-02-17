<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\OAuth2\Tests;

use Webiny\Component\Http\Request;
use Webiny\Component\OAuth2\OAuth2;
use Webiny\Component\OAuth2\OAuth2Loader;

/**
 * Class OAuth2LoaderTest
 * @package Webiny\Component\OAuth2\Tests
 */
class OAuth2LoaderTest extends \PHPUnit_Framework_TestCase
{

    const CONFIG = '/ExampleConfig.yaml';

    public function testGetInstance()
    {
        OAuth2::setConfig(realpath(__DIR__ . '/' . self::CONFIG));

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