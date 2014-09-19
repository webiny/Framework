<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Rest\Tests\Response;

use Webiny\Component\Annotations\Annotations;
use Webiny\Component\Cache\Cache;
use Webiny\Component\Rest\Response\RequestBag;
use Webiny\Component\Rest\Rest;
use Webiny\Component\Rest\Tests\Mocks\MockCacheTestApiClass;

class CacheTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        Annotations::setConfig(__DIR__ . '/../Mocks/MockAnnotationsConfig.yaml');
        Cache::setConfig(__DIR__ . '/../Mocks/MockCacheConfig.yaml');
        Rest::setConfig(__DIR__ . '/../Mocks/MockRestConfig.yaml');
    }

    public function testGetFromCacheFalse()
    {
        $requestBag = new RequestBag();
        // populate request bag and point the cache key creation to the mocked class
        $requestBag->setApi('CacheTest')
                   ->setMethodData(['cache' => ['ttl' => 100]])
                   ->setClassInstance(new MockCacheTestApiClass())
                   ->setClassData(['cacheKeyInterface' => true]);

        \Webiny\Component\Rest\Response\Cache::purgeResult($requestBag);
        $result = \Webiny\Component\Rest\Response\Cache::getFromCache($requestBag);
        $this->assertFalse($result);
    }

    public function testSaveCallbackResult()
    {
        $requestBag = new RequestBag();
        // populate request bag and point the cache key creation to the mocked class
        $requestBag->setApi('CacheTest')
                   ->setMethodData(['cache' => ['ttl' => 100]])
                   ->setClassInstance(new MockCacheTestApiClass())
                   ->setClassData(['cacheKeyInterface' => true]);

        $result = \Webiny\Component\Rest\Response\Cache::saveResult($requestBag, 'my result');
        $this->assertTrue($result);
    }

    public function testGetFromCacheTrue()
    {
        $requestBag = new RequestBag();
        // populate request bag and point the cache key creation to the mocked class
        $requestBag->setApi('CacheTest')
                   ->setMethodData(['cache' => ['ttl' => 100]])
                   ->setClassInstance(new MockCacheTestApiClass())
                   ->setClassData(['cacheKeyInterface' => true]);

        \Webiny\Component\Rest\Response\Cache::saveResult($requestBag, 'my result');

        $result = \Webiny\Component\Rest\Response\Cache::getFromCache($requestBag);
        $this->assertSame('my result', $result);
    }

    public function testPurgeResult()
    {
        $requestBag = new RequestBag();
        // populate request bag and point the cache key creation to the mocked class
        $requestBag->setApi('CacheTest')
                   ->setMethodData(['cache' => ['ttl' => 100]])
                   ->setClassInstance(new MockCacheTestApiClass())
                   ->setClassData(['cacheKeyInterface' => true]);

        \Webiny\Component\Rest\Response\Cache::saveResult($requestBag, 'my result');

        $result = \Webiny\Component\Rest\Response\Cache::getFromCache($requestBag);
        $this->assertSame('my result', $result);

        \Webiny\Component\Rest\Response\Cache::purgeResult($requestBag);

        $result = \Webiny\Component\Rest\Response\Cache::getFromCache($requestBag);
        $this->assertFalse($result);
    }
}