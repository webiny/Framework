<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Rest\Tests\Parser;

use Webiny\Component\Annotations\Annotations;
use Webiny\Component\Rest\Parser\MethodParser;
use Webiny\Component\Rest\Rest;

class MethodParserTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        Annotations::setConfig(__DIR__ . '/../Mocks/MockAnnotationsConfig.yaml');
        Rest::setConfig(__DIR__ . '/../Mocks/MockRestConfig.yaml');
    }

    public function testConstruct()
    {
        $className = '\Webiny\Component\Rest\Tests\Mocks\MockApiClass';
        $reflection = new \ReflectionClass($className);
        $method = $reflection->getMethod('someMethod');
        $instance = new MethodParser('\Webiny\Component\Rest\Tests\Mocks\MockApiClass', $method);
        $this->assertInstanceOf('\Webiny\Component\Rest\Parser\MethodParser', $instance);
    }

    public function testParse()
    {
        $className = '\Webiny\Component\Rest\Tests\Mocks\MockApiClass';
        $reflection = new \ReflectionClass($className);
        $method = $reflection->getMethod('someMethod');
        $instance = new MethodParser('\Webiny\Component\Rest\Tests\Mocks\MockApiClass', $method);
        $parsedMethod = $instance->parse();
        $this->assertInstanceOf('\Webiny\Component\Rest\Parser\ParsedMethod', $parsedMethod);

        // validate parsed method
        $this->assertSame('someMethod', $parsedMethod->name);
        $this->assertSame('some-method', $parsedMethod->urlPattern);
        $this->assertSame('post', $parsedMethod->method);
        $this->assertSame('SECRET', $parsedMethod->role);
        $this->assertSame(['ttl' => '3600'], $parsedMethod->cache);
        $this->assertNotFalse($parsedMethod->default);
        $header = [
            'cache'  => ['expires' => '3600'],
            'status' => [
                'success'      => '201',
                'error'        => '403',
                'errorMessage' => 'No Author for specified id.'
            ]
        ];
        $this->assertSame($header, $parsedMethod->header);
        $this->assertCount(3, $parsedMethod->params);
    }

    public function testParseSimpleMethod()
    {
        $className = '\Webiny\Component\Rest\Tests\Mocks\MockApiClass';
        $reflection = new \ReflectionClass($className);
        $method = $reflection->getMethod('simpleMethod');
        $instance = new MethodParser('\Webiny\Component\Rest\Tests\Mocks\MockApiClass', $method);
        $parsedMethod = $instance->parse();
        $this->assertInstanceOf('\Webiny\Component\Rest\Parser\ParsedMethod', $parsedMethod);

        // validate parsed method
        $this->assertSame('simpleMethod', $parsedMethod->name);
        $this->assertSame('simple-method', $parsedMethod->urlPattern);
        $this->assertSame('get', $parsedMethod->method);
        $this->assertFalse($parsedMethod->role);
        $this->assertSame(['ttl' => 0], $parsedMethod->cache);
        $this->assertFalse($parsedMethod->default);
        $header = [
            'cache'  => ['expires' => 0],
            'status' => [
                'success'      => 200,
                'error'        => 404,
                'errorMessage' => ''
            ]
        ];
        $this->assertSame($header, $parsedMethod->header);
        $this->assertCount(0, $parsedMethod->params);
    }

}