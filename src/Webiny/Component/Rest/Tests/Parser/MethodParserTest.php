<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Rest\Tests\Parser;

use Webiny\Component\Annotations\Annotations;
use Webiny\Component\Rest\Parser\MethodParser;
use Webiny\Component\Rest\Parser\ParsedMethod;
use Webiny\Component\Rest\Rest;
use Webiny\Component\Rest\Tests\Mocks\MockApiClass;

class MethodParserTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        Annotations::setConfig(__DIR__ . '/../Mocks/MockAnnotationsConfig.yaml');
        Rest::setConfig(__DIR__ . '/../Mocks/MockRestConfig.yaml');
    }

    public function testConstruct()
    {
        $reflection = new \ReflectionClass(MockApiClass::class);
        $method = $reflection->getMethod('someMethod');
        $instance = new MethodParser([MockApiClass::class], $method, true);
        $this->assertInstanceOf(MethodParser::class, $instance);
    }

    public function testParse()
    {
        $reflection = new \ReflectionClass(MockApiClass::class);
        $method = $reflection->getMethod('someMethod');
        $instance = new MethodParser([new \ReflectionClass(MockApiClass::class)], $method, true);
        $parsedMethod = $instance->parse();
        $this->assertInstanceOf(ParsedMethod::class, $parsedMethod);

        // validate parsed method
        $this->assertSame('someMethod', $parsedMethod->name);
        $this->assertSame('some-method/([^/]+)/([^/]+)/([\d]+)/', $parsedMethod->urlPattern);
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
        $reflection = new \ReflectionClass(MockApiClass::class);
        $method = $reflection->getMethod('simpleMethod');
        $instance = new MethodParser([new \ReflectionClass(MockApiClass::class)], $method, true);
        $parsedMethod = $instance->parse();
        $this->assertInstanceOf(ParsedMethod::class, $parsedMethod);

        // validate parsed method
        $this->assertSame('simpleMethod', $parsedMethod->name);
        $this->assertSame('simple-method/', $parsedMethod->urlPattern);
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