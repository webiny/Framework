<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Rest\Tests\Compiler;

use Webiny\Component\Annotations\Annotations;
use Webiny\Component\Rest\Compiler\Cache;
use Webiny\Component\Rest\Compiler\Compiler;
use Webiny\Component\Rest\Parser\Parser;
use Webiny\Component\Rest\Rest;

class CompilerTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        Annotations::setConfig(__DIR__ . '/../Mocks/MockAnnotationsConfig.yaml');
        Rest::setConfig(__DIR__ . '/../Mocks/MockRestConfig.yaml');
    }

    public function testConstructor()
    {
        $instance = new Compiler('ExampleApi');
        $this->assertInstanceOf('Webiny\Component\Rest\Compiler\Compiler', $instance);
    }

    public function testWriteCacheFiles()
    {
        $parser = new Parser();
        $parserApi = $parser->parseApi('Webiny\Component\Rest\Tests\Mocks\MockApiClass');

        $instance = new Compiler('ExampleApi');
        $instance->writeCacheFiles($parserApi);

        // now let's validate what was written
        $cRoot = Rest::getConfig()->ExampleApi->CompilePath;
        $cache = Cache::getCacheContent($cRoot . '/ExampleApi/Webiny_Component_Rest_Tests_Mocks_MockApiClass/v1.0.php');

        $this->assertSame($cache['class'], 'Webiny\Component\Rest\Tests\Mocks\MockApiClass');
        $this->assertSame($cache['version'], '1.0');
        $this->assertInternalType('array', $cache['post']);
        $this->assertCount(1, $cache['post']);
        $this->assertCount(1, $cache['get']);
        $this->assertNotFalse($cache['post']['some-method/([\\w-]+)/([\\w-]+)/([\\d]+)/']);

        $method = $cache['post']['some-method/([\\w-]+)/([\\w-]+)/([\\d]+)/'];
        $this->assertNotNull($method['default']);
        $this->assertSame('SECRET', $method['role']);
        $this->assertSame('someMethod', $method['method']);
        $this->assertSame('some-method', $method['urlPattern']);
        $this->assertSame('3600', $method['cache']['ttl']);
        $this->assertSame(['expires' => '3600'], $method['header']['cache']);
        $this->assertSame('201', $method['header']['status']['success']);
        $this->assertSame('403', $method['header']['status']['error']);
        $this->assertSame('No Author for specified id.', $method['header']['status']['errorMessage']);
        $this->assertNotNull($method['rateControl']['ignore']);
        $this->assertCount(3, $method['params']);

        $param = $method['params']['param1'];
        $this->assertTrue($param['required']);
        $this->assertSame('string', $param['type']);
        $this->assertNull($param['default']);

        $param = $method['params']['param2'];
        $this->assertFalse($param['required']);
        $this->assertSame('string', $param['type']);
        $this->assertSame('default', $param['default']);

        $param = $method['params']['param3'];
        $this->assertFalse($param['required']);
        $this->assertSame('integer', $param['type']);
        $this->assertSame(22, $param['default']);

        $method = $cache['get']['simple-method/'];
        $this->assertFalse($method['default']);
        $this->assertSame(false, $method['role']);
        $this->assertSame('simpleMethod', $method['method']);
        $this->assertSame('simple-method', $method['urlPattern']);
        $this->assertSame(0, $method['cache']['ttl']);
        $this->assertSame(['expires' => 0], $method['header']['cache']);
        $this->assertSame(200, $method['header']['status']['success']);
        $this->assertSame(404, $method['header']['status']['error']);
        $this->assertSame('', $method['header']['status']['errorMessage']);
        $this->assertFalse(isset($method['rateControl']['ignore']));
        $this->assertCount(0, $method['params']);
    }
}