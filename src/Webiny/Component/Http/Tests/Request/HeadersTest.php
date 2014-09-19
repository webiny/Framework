<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Http\Tests\Request;


use Webiny\Component\Http\Http;
use Webiny\Component\Http\Request\Headers;

class HeadersTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Http::setConfig(__DIR__ . '/../ExampleConfig.yaml');
    }

    public function testConstructor()
    {
        $headers = new Headers();
        $this->assertInstanceOf('\Webiny\Component\Http\Request\Headers', $headers);
    }

    public function testGet()
    {
        $_SERVER['HTTP_HOST'] = "localhost";

        $headers = new Headers();

        $this->assertSame("localhost", $headers->get("Host"));
        $this->assertNull($headers->get("doesnt_exist"));
        $this->assertSame("utf8", $headers->get("Encoding", "utf8"));
    }

    public function testGetAll()
    {
        $_SERVER['HTTP_HOST'] = "localhost";

        $headers = new Headers();
        $this->assertSame(['Host' => 'localhost'], $headers->getAll());
    }
}