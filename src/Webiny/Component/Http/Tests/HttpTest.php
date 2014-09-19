<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Http\Tests;

use Webiny\Component\Http\Http;

class HttpTest extends \PHPUnit_Framework_TestCase
{
    const CONFIG = '/ExampleConfig.yaml';

    public function testSetConfig()
    {
        Http::setConfig(realpath(__DIR__ . '/' . self::CONFIG));
    }

    public function testGetConfig()
    {
        $this->assertInstanceOf('\Webiny\Component\Config\ConfigObject', Http::getConfig());
    }
}