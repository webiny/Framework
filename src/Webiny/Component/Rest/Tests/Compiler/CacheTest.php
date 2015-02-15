<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Rest\Tests\Compiler;

use Webiny\Component\Annotations\Annotations;
use Webiny\Component\Rest\Compiler\Cache;
use Webiny\Component\Rest\Rest;

class CacheTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        //Annotations::setConfig(__DIR__ . '/../Mocks/MockAnnotationsConfig.yaml');
        Rest::setConfig(__DIR__ . '/../Mocks/MockRestConfig.yaml');
    }

    public function testIsCacheValidFalse()
    {
        $cacheFile = Cache::getCacheFilename('ExampleApi', 'Webiny\Component\Rest\Tests\Mocks\MockApiClass', 'current');
        @unlink($cacheFile);
        $isCacheValid = Cache::isCacheValid('ExampleApi', 'Webiny\Component\Rest\Tests\Mocks\MockApiClass');
        $this->assertFalse($isCacheValid);
    }

    /**
     * @expectedException \Webiny\Component\Rest\RestException
     * @expectedExceptionMessage Unable to validate the cache
     */
    public function testIsCacheValidException()
    {
        // for cache to be valid, we must create a dummy file
        file_put_contents(Cache::getCacheFilename('ExampleApi', 'FooClass', 'current'), 'foo');

        $isCacheValid = Cache::isCacheValid('ExampleApi', 'FooClass');
        $this->assertTrue($isCacheValid);
    }

    public function testIsCacheValidTrue()
    {
        // for cache to be valid, we must create a dummy file
        $cacheFile = Cache::getCacheFilename('ExampleApi', 'Webiny\Component\Rest\Tests\Mocks\MockApiClass', 'current');
        @unlink($cacheFile);
        file_put_contents($cacheFile, 'foo');

        $isCacheValid = Cache::isCacheValid('ExampleApi', 'Webiny\Component\Rest\Tests\Mocks\MockApiClass');
        $this->assertTrue($isCacheValid);
    }

    public function testGetGetCacheContent()
    {
        $cacheFile = Cache::getCacheFilename('ExampleApi', 'Webiny\Component\Rest\Tests\Mocks\MockApiClass', 'current');
        @unlink($cacheFile);
        file_put_contents($cacheFile, '<?php return "foo";');

        $content = Cache::getCacheContent($cacheFile);
        $this->assertSame('foo', $content);
    }

    /**
     * @expectedException \Webiny\Component\Rest\RestException
     * @expectedExceptionMessage Cache file doesn't exist
     */
    public function testGetCacheContentException()
    {
        Cache::getCacheContent('Foo');
    }

    /**
     * @dataProvider getCacheFilenameProvider
     *
     * @param $api
     * @param $class
     * @param $version
     * @param $expected
     */
    public function testGetCacheFilename($api, $class, $version, $expected)
    {
        $cachePathRoot = Rest::getConfig()->ExampleApi->CompilePath;
        $this->assertSame($cachePathRoot . '/' . $expected, Cache::getCacheFilename($api, $class, $version));
    }

    public function getCacheFilenameProvider()
    {
        return [
            [
                'ExampleApi',
                'FooClass',
                'latest',
                'ExampleApi/FooClass/latest.php'
            ],
            [
                'ExampleApi',
                'Webiny\Root\FooClass',
                '1.1',
                'ExampleApi/Webiny_Root_FooClass/v1.1.php'
            ],
        ];
    }
}