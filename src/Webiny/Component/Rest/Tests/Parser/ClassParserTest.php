<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Rest\Tests\Parser;

use Webiny\Component\Annotations\Annotations;
use Webiny\Component\Rest\Parser\ClassParser;
use Webiny\Component\Rest\Parser\ParsedClass;
use Webiny\Component\Rest\Rest;
use Webiny\Component\Rest\Tests\Mocks\MockApiClass;
use Webiny\Component\Rest\Tests\Mocks\MockEmptyClass;

class ClassParserTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        Annotations::setConfig(__DIR__ . '/../Mocks/MockAnnotationsConfig.yaml');
        Rest::setConfig(__DIR__ . '/../Mocks/MockRestConfig.yaml');
    }

    public function testConstruct()
    {
        $instance = new ClassParser(MockApiClass::class, true);
        $this->assertInstanceOf(ClassParser::class, $instance);
    }

    /**
     * @expectedException \Webiny\Component\Rest\RestException
     * @expectedExceptionMessage Parser: Unable to parse class
     */
    public function testConstructException()
    {
        $instance = new ClassParser('FooClass', true);
    }

    /**
     * @expectedException \Webiny\Component\Rest\RestException
     * @expectedExceptionMessage Parser: The class
     */
    public function testNoMethods()
    {
        new ClassParser(MockEmptyClass::class, true);
    }

    public function testGetParsedClass()
    {
        $instance = new ClassParser(MockApiClass::class, true);
        $parsedClass = $instance->getParsedClass();
        $this->assertInstanceOf(ParsedClass::class, $parsedClass);

        $this->assertSame(MockApiClass::class, $parsedClass->class);
        $this->assertCount(2, $parsedClass->parsedMethods);
    }


}