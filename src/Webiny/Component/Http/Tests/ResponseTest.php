<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Http\Tests;

use Webiny\Component\Http\Http;
use Webiny\Component\Http\Response;

class ResponseTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Http::setConfig(__DIR__ . '/ExampleConfig.yaml');
    }

    public function testConstructor()
    {
        $response = new Response();
        $this->assertInstanceOf('\Webiny\Component\Http\Response', $response);
    }

    public function testCreate()
    {
        $response = Response::create();
        $this->assertInstanceOf('\Webiny\Component\Http\Response', $response);
    }

    public function testSetContent()
    {
        $response = Response::create();
        $response->setContent('Hello World!');
    }

    public function testGetContent()
    {
        $response = Response::create();
        $response->setContent('Hello World!');
        $this->assertSame('Hello World!', $response->getContent());
    }

    public function testSetStatusCode()
    {
        $response = Response::create();
        $response->setStatusCode(404);
    }

    public function testGetStatusCode()
    {
        $response = Response::create();
        $response->setStatusCode(404);
        $this->assertSame(404, $response->getStatusCode());
    }

    /**
     * @expectedException \Webiny\Component\Http\Response\ResponseException
     */
    public function testSetStatusCodeException()
    {
        $response = Response::create();
        $response->setStatusCode(666);
    }

    public function testSetContentType()
    {
        $response = Response::create();
        $response->setContentType('image/jpeg');
    }

    public function testGetContentType()
    {
        $response = Response::create();
        $response->setContentType('image/jpeg');
        $this->assertSame('image/jpeg', $response->getContentType());
    }

    public function testSetCharset()
    {
        $response = Response::create();
        $response->setCharset('UTF-8');
    }

    public function testGetCharset()
    {
        $response = Response::create();
        $response->setCharset('UTF-8');
        $this->assertSame('UTF-8', $response->getCharset());
    }

    public function setHeader()
    {
        $response = Response::create();
        $response->setHeader('test-header', 'test-value');
    }

    public function getHeaders()
    {
        $response = Response::create();
        $response->setHeader('test-header', 'test-value');
        $this->assertArrayHasKey('test-header', $response->getHeaders());
        $this->assertArrayHasValue('test-value', $response->getHeaders());
    }

    public function testSetNotModified()
    {
        $response = Response::create();
        $response->setContent('some content');
        $response->setStatusCode(200);

        $response->setAsNotModified();

        $this->assertSame('', $response->getContent()); // content must be blank
        $this->assertSame(304, $response->getStatusCode());
    }

    public function testCacheControl()
    {
        $response = Response::create();
        $this->assertInstanceOf('\Webiny\Component\Http\Response\CacheControl', $response->cacheControl());
    }
}