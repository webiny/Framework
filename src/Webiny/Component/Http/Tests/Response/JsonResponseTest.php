<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Http\Tests\Response;

use Webiny\Component\Http\Http;
use Webiny\Component\Http\Response\JsonResponse;
use Webiny\Component\StdLib\StdObject\ArrayObject\ArrayObject;

class JsonResponseTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Http::setConfig(__DIR__ . '/../ExampleConfig.yaml');
    }

    public function testConstructor()
    {
        $jr = new JsonResponse([]);
        $this->assertInstanceOf('\Webiny\Component\Http\Response\JsonResponse', $jr);
        $this->assertInstanceOf('\Webiny\Component\Http\Response', $jr);
    }

    public function testArrayConstructor()
    {
        $array = ['foo' => 'value'];
        $jr = new JsonResponse($array);
        $this->assertSame(json_encode($array), $jr->getContent());
    }

    public function testArrayObjectConstructor()
    {
        $arrayObject = new ArrayObject(['foo' => 'value']);
        $jr = new JsonResponse($arrayObject);
        $this->assertSame(json_encode($arrayObject->val()), $jr->getContent());
    }

    public function testContentType()
    {
        $jr = new JsonResponse('');
        $this->assertSame('application/json', $jr->getContentType());
    }
}