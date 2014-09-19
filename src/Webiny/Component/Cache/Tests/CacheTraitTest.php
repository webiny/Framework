<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Cache\Tests;

use Webiny\Component\Cache\Cache;
use Webiny\Component\Cache\CacheTrait;
use Webiny\Component\ClassLoader\ClassLoader;

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
        $this->assertInstanceOf('\Webiny\Component\Cache\CacheStorage', $this->cache('SomeOtherCache'));
    }
}