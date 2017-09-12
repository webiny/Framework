<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Http\Tests;


use Webiny\Component\Http\Http;
use Webiny\Component\Http\Request;

class RequestTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Http::setConfig(__DIR__ . '/ExampleConfig.yaml');
    }

    public function testConstructor()
    {
        $request = Request::getInstance();
        $this->assertInstanceOf(Request::class, $request);
    }

    public function testQuery()
    {
        Request::deleteInstance();
        $_GET = ["name" => "jack"];
        $this->assertSame("jack", Request::getInstance()->query("name"));
        $this->assertNull(Request::getInstance()->query("no-name"));
        $this->assertSame("default", Request::getInstance()->query("no-name-2", "default"));
    }

    public function testPost()
    {
        Request::deleteInstance();
        $_POST = ["name" => "jack"];
        $this->assertSame("jack", Request::getInstance()->post("name"));
        $this->assertNull(Request::getInstance()->post("no-name"));
        $this->assertSame("default", Request::getInstance()->post("no-name-2", "default"));
    }

    public function testHeader()
    {
        Request::deleteInstance();
        $_SERVER['HTTP_HOST'] = "localhost";
        $this->assertSame("localhost", Request::getInstance()->header("Host"));
        $this->assertNull(Request::getInstance()->header("NO-HTTP_HOST"));
        $this->assertSame("default", Request::getInstance()->header("NO-HTTP_HOST-2", "default"));
    }

    public function testEnv()
    {
        Request::deleteInstance();
        $_ENV = ["name" => "jack"];
        $this->assertSame("jack", Request::getInstance()->env("name"));
        $this->assertNull(Request::getInstance()->env("no-name"));
        $this->assertSame("default", Request::getInstance()->env("no-name-2", "default"));
    }

    public function testServer()
    {
        Request::deleteInstance();
        $server = Request::getInstance()->server();
        $this->assertInstanceOf(Request\Server::class, $server);
    }

    public function testFiles()
    {
        $_FILES = [
            "test_file" => [
                "name"     => "some_image.jpg",
                "type"     => "image/jpeg",
                "tmp_name" => "/tmp/a.jpg",
                "size"     => "2048",
                "error"    => ""
            ]
        ];

        Request::deleteInstance();
        $file = Request::getInstance()->files('test_file');
        $this->assertInstanceOf(Request\Files\File::class, $file);
    }

    public function testGetTrustedProxies()
    {
        $trustedProxies = Request::getInstance()->getTrustedProxies();
        $this->assertSame('127.0.0.1', $trustedProxies[0]);
    }

    public function testGetTrustedHeaders()
    {
        $thAssert = [
            'client_ip'    => 'X_FORWARDED_FOR',
            'client_host'  => 'X_FORWARDED_HOST',
            'client_proto' => 'X_FORWARDED_PROTO',
            'client_port'  => 'X_FORWARDED_PORT'
        ];
        $trustedHeaders = Request::getInstance()->getTrustedHeaders();
        $this->assertSame($thAssert, $trustedHeaders);
    }
}