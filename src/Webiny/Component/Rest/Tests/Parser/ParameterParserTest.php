<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Rest\Tests\Parser;

use Webiny\Component\Annotations\Annotations;
use Webiny\Component\Annotations\AnnotationsTrait;
use Webiny\Component\Config\ConfigObject;
use Webiny\Component\Rest\Parser\ParameterParser;
use Webiny\Component\Rest\Rest;

class ParameterParserTest extends \PHPUnit_Framework_TestCase
{
    use AnnotationsTrait;

    public function setUp()
    {
        Annotations::setConfig(__DIR__ . '/../Mocks/MockAnnotationsConfig.yaml');
        Rest::setConfig(__DIR__ . '/../Mocks/MockRestConfig.yaml');
    }

    public function testConstruct()
    {
        $className = '\Webiny\Component\Rest\Tests\Mocks\MockApiClass';
        $methodName = 'someMethod';

        // get method annotations
        $annotations = $this->annotationsFromMethod($className, $methodName);
        $paramAnnotations = $annotations->get('param', new ConfigObject([]));

        // extract params
        $reflection = new \ReflectionClass($className);
        $method = $reflection->getMethod('someMethod');
        $params = $method->getParameters();

        $instance = new ParameterParser($params, $paramAnnotations);
        $this->assertInstanceOf('\Webiny\Component\Rest\Parser\ParameterParser', $instance);
    }

    public function testParse()
    {
        $className = '\Webiny\Component\Rest\Tests\Mocks\MockApiClass';
        $methodName = 'someMethod';

        // get method annotations
        $annotations = $this->annotationsFromMethod($className, $methodName);
        $paramAnnotations = $annotations->get('param', new ConfigObject([]));

        // extract params
        $reflection = new \ReflectionClass($className);
        $method = $reflection->getMethod('someMethod');
        $params = $method->getParameters();

        $instance = new ParameterParser($params, $paramAnnotations);
        $parsedParams = $instance->parse();
        $this->assertInternalType('array', $parsedParams);

        $this->assertInstanceOf('\Webiny\Component\Rest\Parser\ParsedParameter', $parsedParams[0]);

        // validate parameters
        $this->assertCount(3, $parsedParams);

        // param 1
        $this->assertSame('param1', $parsedParams[0]->name);
        $this->assertSame('string', $parsedParams[0]->type);
        $this->assertTrue($parsedParams[0]->required);

        // param 2
        $this->assertSame('param2', $parsedParams[1]->name);
        $this->assertSame('string', $parsedParams[1]->type);
        $this->assertFalse($parsedParams[1]->required);

        // param 3
        $this->assertSame('param3', $parsedParams[2]->name);
        $this->assertSame('integer', $parsedParams[2]->type);
        $this->assertFalse($parsedParams[2]->required);
    }


}