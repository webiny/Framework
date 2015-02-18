<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Http\Tests;


use Webiny\Component\Http\Http;
use Webiny\Component\Http\Cookie;

class CookieTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Http::setConfig(__DIR__ . '/ExampleConfig.yaml');
    }

    public function testConstructor()
    {
        $cookie = Cookie::getInstance();
        $this->assertInstanceOf('\Webiny\Component\Http\Cookie', $cookie);
    }

    public function testSave()
    {
        $cookie = Cookie::getInstance();
        $result = $cookie->save("test_cookie", "test_value", 600);
        $this->assertTrue($result);
    }

    public function testGet()
    {
        $cookie = Cookie::getInstance();
        $cookie->save("test_cookie_get", "test_value", 600);
        $cookieResult = $cookie->get("test_cookie_get");
        $this->assertSame("test_value", $cookieResult);
        $this->assertFalse($cookie->get("some_non_existing_cookie"));
    }

    public function testDelete()
    {
        // save
        $cookie = Cookie::getInstance();
        $cookie->save("test_cookie_delete", "test_value", 600);
        // get
        $cookieResult = $cookie->get("test_cookie_delete");
        $this->assertSame("test_value", $cookieResult);
        // delete
        $cookie->delete("test_cookie_delete");
        $cookieResult = $cookie->get("test_cookie_delete");
        $this->assertFalse($cookieResult);

        // delete 2
        $this->assertTrue($cookie->delete("some_non_existing_cookie"));
    }


}