<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Cache\Tests;

use Webiny\Component\Cache\Cache;
use Webiny\Component\Cache\CacheStorage;

class CacheStorageTest extends \PHPUnit_Framework_TestCase
{
    const CACHE_KEY = 'TestKey';
    const MEMCACHE_IP = '127.0.0.1';
    const CONFIG = 'ExampleConfig.yaml';

    /**
     * @var CacheStorage
     */
    private $instance;

    public function setUp()
    {
        Cache::setConfig(realpath(__DIR__ . '/' . self::CONFIG));
        $this->instance = Cache::SessionArray();
    }

    public function testConstructor()
    {
        $this->assertInstanceOf('Webiny\Component\Cache\CacheStorage', $this->instance);
    }

    public function testSave()
    {
        $this->instance->save(self::CACHE_KEY, 'some value', 3600, [
            'test',
            'unit',
            'tag'
        ]);
    }

    public function testRead()
    {
        $this->instance->save(self::CACHE_KEY, 'some value', 3600, [
            'test',
            'unit',
            'tag'
        ]);

        $this->assertSame('some value', $this->instance->read(self::CACHE_KEY));
    }

    public function testDelete()
    {
        $this->instance->save(self::CACHE_KEY, 'some value', 3600, [
            'test',
            'unit',
            'tag'
        ]);
        $this->assertSame('some value', $this->instance->read(self::CACHE_KEY));
        $this->instance->delete(self::CACHE_KEY);

        $this->assertFalse($this->instance->read(self::CACHE_KEY));

    }

    public function testDeleteByTags()
    {

        $this->instance->save(self::CACHE_KEY, 'some value', 3600, [
            'test',
            'unit',
            'tag'
        ]);
        $this->assertSame('some value', $this->instance->read(self::CACHE_KEY));
        $this->instance->deleteByTags('test');

        $this->assertFalse($this->instance->read(self::CACHE_KEY));

    }

}