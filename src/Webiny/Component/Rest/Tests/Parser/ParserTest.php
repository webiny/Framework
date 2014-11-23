<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Rest\Tests\Parser;

use Webiny\Component\Annotations\Annotations;
use Webiny\Component\Rest\Parser\Parser;
use Webiny\Component\Rest\Rest;

class ParserTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        Annotations::setConfig(__DIR__ . '/../Mocks/MockAnnotationsConfig.yaml');
        Rest::setConfig(__DIR__ . '/../Mocks/MockRestConfig.yaml');
    }

    public function testConstruct()
    {
        $instance = new Parser();
        $this->assertInstanceOf('\Webiny\Component\Rest\Parser\Parser', $instance);
    }

    public function testParseApi()
    {
        $instance = new Parser();
        $parsedApi = $instance->parseApi('Webiny\Component\Rest\Tests\Mocks\MockApiClass', true);

        $this->assertCount(2, $parsedApi->versions);
        $this->assertSame('1.0', $parsedApi->currentVersion);
        $this->assertSame('1.1', $parsedApi->latestVersion);
    }


}