<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Cache\Tests;

use Webiny\Component\Cache\Cache;

class CacheStorageTest extends \PHPUnit_Framework_TestCase
{
    const CACHE_KEY = 'TestKey';
    const MEMCACHE_IP = '192.168.58.20';
    const CONFIG = 'ExampleConfig.yaml';

    /**
     * @dataProvider driverSet
     */
    public function testConstructor($cache)
    {
        $this->assertInstanceOf('Webiny\Component\Cache\CacheStorage', $cache);
    }

    /**
     * @dataProvider driverSet
     */
    public function testSave($cache)
    {
        $cache->save(self::CACHE_KEY, 'some value', 3600, [
                'test',
                'unit',
                'tag'
            ]
        );
    }

    /**
     * @dataProvider driverSet
     */
    public function testRead($cache)
    {
        $this->assertSame('some value', $cache->read(self::CACHE_KEY));
    }

    /**
     * @dataProvider driverSet
     */
    public function testDelete($cache)
    {

        $cache->delete(self::CACHE_KEY);

        $this->assertTrue(self::CACHE_KEY == false || $cache->read(self::CACHE_KEY) == null);

    }

    /**
     * @dataProvider driverSet
     */
    public function testDeleteByTags($cache)
    {

        $cache->save(self::CACHE_KEY, 'some value', 3600);
        $cache->deleteByTags('test');

        $this->assertTrue($cache->read(self::CACHE_KEY) == false || $cache->read(self::CACHE_KEY) == null);

    }

    public function driverSet()
    {
        Cache::setConfig(realpath(__DIR__ . '/' . self::CONFIG));

        return [
            [Cache::Memcache(self::MEMCACHE_IP)]
        ];
    }

}