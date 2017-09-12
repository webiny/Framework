<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Cache\Tests;

use Webiny\Component\Cache\Bridge\Memory\Memcache;
use Webiny\Component\Cache\Cache;
use Webiny\Component\Config\ConfigObject;

class CacheTest extends \PHPUnit_Framework_TestCase
{
    const CONFIG = '/ExampleConfig.yaml';

    public function testSetConfig()
    {
        Cache::setConfig(realpath(__DIR__ . '/' . self::CONFIG));
    }

    public function testGetConfig()
    {
        $this->assertInstanceOf(ConfigObject::class, Cache::getConfig());
    }

    public function testConfigServices()
    {
        $this->assertSame(Cache::class, Cache::getConfig()->get('Services.TestCache.Factory'));
        $this->assertSame(Memcache::class, Cache::getConfig()->get('Bridges.Memcache'));
        $this->assertFalse(Cache::getConfig()->get('Bridges.FakeBridge', false));
    }
}