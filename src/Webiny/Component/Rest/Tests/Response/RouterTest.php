<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Rest\Tests\Response;

use Webiny\Component\Annotations\Annotations;
use Webiny\Component\Rest\Compiler\Cache;
use Webiny\Component\Rest\Compiler\CacheDrivers\ArrayDriver;
use Webiny\Component\Rest\Response\Router;
use Webiny\Component\Rest\Compiler\Compiler;
use Webiny\Component\Rest\Parser\Parser;
use Webiny\Component\Rest\Rest;

/**
 * Class RouterTest
 * @package Webiny\Component\Rest\Tests\Response
 * @runInSeparateProcess
 */
class RouterTest extends \PHPUnit_Framework_TestCase
{

    static protected $cache;

    public function setUp()
    {
        Annotations::setConfig(__DIR__ . '/../Mocks/MockAnnotationsConfig.yaml');
        Rest::setConfig(__DIR__ . '/../Mocks/MockRestConfig.yaml');

        // we need to create the cache files so we can test the router
        $parser = new Parser();
        $parserApi = $parser->parseApi('Webiny\Component\Rest\Tests\Mocks\MockApiClassRouter', true);

        self::$cache = new Cache(new ArrayDriver());
        $instance = new Compiler('ExampleApi', true, self::$cache);
        $instance->writeCacheFiles($parserApi);
    }

    public function testConstruct()
    {
        $instance = new Router('ExampleApi', 'Webiny\Component\Rest\Tests\Mocks\MockApiClassRouter', true,
            self::$cache);
        $this->assertInstanceOf('Webiny\Component\Rest\Response\Router', $instance);
    }

    public function testSetGetUrl()
    {
        $url = 'http://api.example.com/mock-api-class-router/';
        $r = new Router('ExampleApi', 'Webiny\Component\Rest\Tests\Mocks\MockApiClassRouter', true, self::$cache);
        $r->setUrl($url);
        $this->assertSame('/mock-api-class-router/', $r->getUrl());
    }

    public function testSetGetMethod()
    {
        $r = new Router('ExampleApi', 'Webiny\Component\Rest\Tests\Mocks\MockApiClassRouter', true, self::$cache);
        $r->setHttpMethod('GET');
        $this->assertSame('get', $r->getMethod());
    }

    /**
     * @expectedException \Webiny\Component\Rest\RestException
     */
    public function testSetMethodException()
    {
        $r = new Router('ExampleApi', 'Webiny\Component\Rest\Tests\Mocks\MockApiClassRouter', true, self::$cache);
        $r->setHttpMethod('foo');
    }

    /**
     * We test if the default post method will be matched.
     */
    public function testProcessRequestDefaultNoParams()
    {
        $url = 'http://api.example.com/mock-api-class-router/';
        $r = new Router('ExampleApi', 'Webiny\Component\Rest\Tests\Mocks\MockApiClassRouter', true, self::$cache);
        $r->setUrl($url);
        $r->setHttpMethod('post');

        $result = $r->processRequest();

        $this->assertSame('testProcessRequestDefaultNoParams', $result->getOutput()['data']);
    }


    /**
     * Test to match a default get method that has one default param.
     */
    public function testProcessRequestOneStringParamNotDefined()
    {
        $url = 'http://api.example.com/mock-api-class-router/';
        $r = new Router('ExampleApi', 'Webiny\Component\Rest\Tests\Mocks\MockApiClassRouter', true, self::$cache);
        $r->setUrl($url);
        $r->setHttpMethod('get');

        $result = $r->processRequest();

        $this->assertSame('testProcessRequestOneStringParamRequired - d3f', $result->getOutput()['data']);
    }

    /**
     * Test to match a default get method that has one default param that is overwritten in url.
     */
    public function testProcessRequestOneStringParamRequiredDefined()
    {
        $url = 'http://api.example.com/mock-api-class-router/defined/';
        $r = new Router('ExampleApi', 'Webiny\Component\Rest\Tests\Mocks\MockApiClassRouter', true, self::$cache);
        $r->setUrl($url);
        $r->setHttpMethod('get');

        $result = $r->processRequest();

        $this->assertSame('testProcessRequestOneStringParamRequired - defined', $result->getOutput()['data']);
    }

    /**
     * Test to match a default get method that has one default param that is overwritten in url.
     */
    public function testProcessRequestOneIntegerParamRequired()
    {
        $url = 'http://api.example.com/mock-api-class-router/test-integer/10/';
        $r = new Router('ExampleApi', 'Webiny\Component\Rest\Tests\Mocks\MockApiClassRouter', true, self::$cache);
        $r->setUrl($url);
        $r->setHttpMethod('get');

        $result = $r->processRequest();

        $this->assertSame('testProcessRequestOneIntegerParamRequired - 10', $result->getOutput()['data']);
    }

    /**
     * Test to match a default get method that has one default param that is overwritten in url.
     */
    public function testProcessRequestOneIntegerParamRequiredNotMatched()
    {
        $url = 'http://api.example.com/mock-api-class-router/test-integer/a/';
        $r = new Router('ExampleApi', 'Webiny\Component\Rest\Tests\Mocks\MockApiClassRouter', true, self::$cache);
        $r->setUrl($url);
        $r->setHttpMethod('get');

        $result = $r->processRequest();

        $this->assertSame('No service matched the request.', $result->getOutput()['errorReport']['message']);
    }

    /**
     * Test to match a default get method that has one default param that is overwritten in url.
     */
    public function testProcessRequestStringIntDefString()
    {
        $url = 'http://api.example.com/mock-api-class-router/str/10/';
        $r = new Router('ExampleApi', 'Webiny\Component\Rest\Tests\Mocks\MockApiClassRouter', true, self::$cache);
        $r->setUrl($url);
        $r->setHttpMethod('get');

        $result = $r->processRequest();

        $this->assertSame('testProcessRequestStringIntDefString - str 10 d3f', $result->getOutput()['data']);
    }

    /**
     * Test to match a default get method that has one default param that is overwritten in url.
     */
    public function testProcessRequestStringIntString()
    {
        $url = 'http://api.example.com/mock-api-class-router/str/10/2xstr';
        $r = new Router('ExampleApi', 'Webiny\Component\Rest\Tests\Mocks\MockApiClassRouter', true, self::$cache);
        $r->setUrl($url);
        $r->setHttpMethod('get');

        $result = $r->processRequest();

        $this->assertSame('testProcessRequestStringIntDefString - str 10 2xstr', $result->getOutput()['data']);
    }

    public function testResourceNamingNoParams()
    {
        $url = 'http://api.example.com/mock-api-class-router/some-function-name/that/has/a/custom-url';

        $r = new Router('ExampleApi', 'Webiny\Component\Rest\Tests\Mocks\MockApiClassRouter', true, self::$cache);
        $r->setUrl($url);
        $r->setHttpMethod('get');

        $result = $r->processRequest();

        $this->assertSame('in fooBar', $result->getOutput()['data']);
    }

    public function testResourceNamingWithParams()
    {
        $url = 'http://api.example.com/mock-api-class-router/some-url/123/name/John Snow';

        $r = new Router('ExampleApi', 'Webiny\Component\Rest\Tests\Mocks\MockApiClassRouter', true, self::$cache);
        $r->setUrl($url);
        $r->setHttpMethod('get');

        $result = $r->processRequest();

        $this->assertSame('123 => John Snow', $result->getOutput()['data']);
    }
}