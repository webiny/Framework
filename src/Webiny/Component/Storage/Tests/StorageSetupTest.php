<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Storage\Tests;

use Webiny\Component\Storage\Storage;

class StorageSetupTest extends \PHPUnit_Framework_TestCase
{
    const CONFIG = '/ExampleConfig.yaml';

    public function testSetConfig()
    {
        Storage::setConfig(realpath(__DIR__). self::CONFIG);
    }

    public function testGetConfig()
    {
        $this->assertInstanceOf('\Webiny\Component\Config\ConfigObject', Storage::getConfig());
    }

    public function testConfigServices()
    {
        $this->assertSame('%StorageClass%', Storage::getConfig()->get('Services.LocalStorage.Class'));
        $this->assertFalse(Storage::getConfig()->get('Bridges.FakeBridge', false));
    }
}