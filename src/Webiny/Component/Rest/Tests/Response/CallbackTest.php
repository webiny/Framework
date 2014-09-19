<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Rest\Tests\Response;

use Webiny\Component\Annotations\Annotations;
use Webiny\Component\Cache\Cache;
use Webiny\Component\Rest\Response\Callback;
use Webiny\Component\Rest\Response\RequestBag;
use Webiny\Component\Rest\Rest;

class CallbackTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        Annotations::setConfig(__DIR__ . '/../Mocks/MockAnnotationsConfig.yaml');
        Cache::setConfig(__DIR__ . '/../Mocks/MockCacheConfig.yaml');
        Rest::setConfig(__DIR__ . '/../Mocks/MockRestConfig.yaml');
    }

    public function testConstruct()
    {
        $instance = new Callback(new RequestBag());
        $this->assertInstanceOf('Webiny\Component\Rest\Response\Callback', $instance);
    }

    public function testGetCallbackResultNoMethodMatched()
    {
        $requestBag = new RequestBag();
        $requestBag->setApi('CacheTest')->setClassData([
                                                           'class'   => 'Webiny\Component\Rest\Tests\Mocks\MockCacheTestApiClass',
                                                           'version' => '1.0'
                                                       ]
        )->setCompileCacheFile('foo.cache.file');

        $callback = new Callback($requestBag);
        $response = $callback->getCallbackResult()->getOutput();

        $this->assertSame('No service matched the request.', $response['errorReport']['message']);
    }

    public function testGetCallbackResult()
    {
        $methodData = [
            'method' => 'testCallback',
            'cache'  => ['ttl' => 0],
            'header' => [
                'cache'  => [
                    'expires' => 0,
                ],
                'status' => [
                    'success'      => 200,
                    'error'        => 404,
                    'errorMessage' => '',
                ],
            ],
        ];

        $requestBag = new RequestBag();
        $requestBag->setApi('CacheTest')->setClassData([
                                                           'class'   => 'Webiny\Component\Rest\Tests\Mocks\MockApiClassCallback',
                                                           'version' => '1.0'
                                                       ]
        )->setCompileCacheFile('foo.cache.file')->setMethodData($methodData)->setMethodParameters([]);

        $callback = new Callback($requestBag);
        $response = $callback->getCallbackResult()->getOutput();

        $this->assertSame('test result', $response['data']);
    }

    public function testGetCallbackResultRestError()
    {
        $methodData = [
            'method' => 'testCallbackRestErrorException',
            'cache'  => ['ttl' => 0],
            'header' => [
                'cache'  => [
                    'expires' => 0,
                ],
                'status' => [
                    'success'      => 200,
                    'error'        => 404,
                    'errorMessage' => '',
                ],
            ],
        ];

        $requestBag = new RequestBag();
        $requestBag->setApi('CacheTest')->setClassData([
                                                           'class'   => 'Webiny\Component\Rest\Tests\Mocks\MockApiClassCallback',
                                                           'version' => '1.0'
                                                       ]
        )->setCompileCacheFile('foo.cache.file')->setMethodData($methodData)->setMethodParameters([]);

        $callback = new Callback($requestBag);
        $response = $callback->getCallbackResult()->getOutput();

        $this->assertSame('This is a rest error.', $response['errorReport']['message']);
    }

    public function testGetCallbackResultException()
    {
        $methodData = [
            'method' => 'testCallbackException',
            'cache'  => ['ttl' => 0],
            'header' => [
                'cache'  => [
                    'expires' => 0,
                ],
                'status' => [
                    'success'      => 200,
                    'error'        => 404,
                    'errorMessage' => '',
                ],
            ],
        ];

        $requestBag = new RequestBag();
        $requestBag->setApi('CacheTest')->setClassData([
                                                           'class'   => 'Webiny\Component\Rest\Tests\Mocks\MockApiClassCallback',
                                                           'version' => '1.0'
                                                       ]
        )->setCompileCacheFile('foo.cache.file')->setMethodData($methodData)->setMethodParameters([]);

        $callback = new Callback($requestBag);
        $response = $callback->getCallbackResult()->getOutput();

        $this->assertSame('There has been an error processing the request.', $response['errorReport']['message']);
    }
}