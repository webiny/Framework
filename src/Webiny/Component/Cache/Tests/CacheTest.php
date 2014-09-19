<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Cache\Tests;

use Webiny\Component\Cache\Cache;

class CacheTest extends \PHPUnit_Framework_TestCase
{
    const CONFIG = '/ExampleConfig.yaml';

    public function testSetConfig()
    {
        Cache::setConfig(realpath(__DIR__ . '/' . self::CONFIG));
    }

    public function testGetConfig()
    {
        $this->assertInstanceOf('\Webiny\Component\Config\ConfigObject', Cache::getConfig());
    }

    public function testConfigServices()
    {
        $this->assertSame('\Webiny\Component\Cache\Cache', Cache::getConfig()->get('Services.TestCache.Factory'));
        $this->assertSame('\Webiny\Component\Cache\Bridge\Memory\Memcache', Cache::getConfig()->get('Bridges.Memcache')
        );
        $this->assertFalse(Cache::getConfig()->get('Bridges.FakeBridge', false));
    }
}