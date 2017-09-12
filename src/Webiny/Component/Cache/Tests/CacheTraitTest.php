<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Cache\Tests;

use Webiny\Component\Cache\Cache;
use Webiny\Component\Cache\CacheStorage;
use Webiny\Component\Cache\CacheTrait;

class CacheTraitTest extends \PHPUnit_Framework_TestCase
{
    use CacheTrait;

    const CONFIG = 'ExampleConfig.yaml';

    public function setUp()
    {
        Cache::setConfig(realpath(__DIR__ . '/' . self::CONFIG));
    }

    public function testCache()
    {
        $this->assertInstanceOf(CacheStorage::class, $this->cache('SomeOtherCache'));
    }
}