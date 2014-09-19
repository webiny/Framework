<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Rest\Tests\Parser;

use Webiny\Component\Annotations\Annotations;
use Webiny\Component\Rest\Parser\ClassParser;
use Webiny\Component\Rest\Rest;

class ClassParserTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        Annotations::setConfig(__DIR__ . '/../Mocks/MockAnnotationsConfig.yaml');
        Rest::setConfig(__DIR__ . '/../Mocks/MockRestConfig.yaml');
    }

    public function testConstruct()
    {
        $instance = new ClassParser('\Webiny\Component\Rest\Tests\Mocks\MockApiClass');
        $this->assertInstanceOf('\Webiny\Component\Rest\Parser\ClassParser', $instance);
    }

    /**
     * @expectedException \Webiny\Component\Rest\RestException
     * @expectedExceptionMessage Parser: Unable to parse class
     */
    public function testConstructException()
    {
        $instance = new ClassParser('FooClass');
    }

    /**
     * @expectedException \Webiny\Component\Rest\RestException
     * @expectedExceptionMessage Parser: The class
     */
    public function testNoMethods()
    {
        $instance = new ClassParser('\Webiny\Component\Rest\Tests\Mocks\MockEmptyClass');
    }

    public function testGetParsedClass()
    {
        $instance = new ClassParser('\Webiny\Component\Rest\Tests\Mocks\MockApiClass');
        $parsedClass = $instance->getParsedClass();
        $this->assertInstanceOf('\Webiny\Component\Rest\Parser\ParsedClass', $parsedClass);

        $this->assertSame('\Webiny\Component\Rest\Tests\Mocks\MockApiClass', $parsedClass->class);
        $this->assertCount(2, $parsedClass->parsedMethods);
    }


}