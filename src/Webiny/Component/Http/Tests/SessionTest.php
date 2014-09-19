<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Http\Tests;


use Webiny\Component\Http\Http;
use Webiny\Component\Http\Session;

class SessionTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Http::setConfig(__DIR__ . '/ExampleConfig.yaml');
    }

    /**
     * @runInSeparateProcess
     */
    public function testConstructor()
    {
        $session = Session::getInstance();;
        $this->assertInstanceOf('\Webiny\Component\Http\Session', $session);
    }

    /**
     * @runInSeparateProcess
     */
    public function testSave()
    {
        $session = Session::getInstance();;
        $session->save("some_id", 123);
        $this->assertArrayHasKey("some_id", $_SESSION);
        $this->assertSame(123, $_SESSION["some_id"]);
    }

    /**
     * @runInSeparateProcess
     */
    public function testGet()
    {
        $session = Session::getInstance();;
        $session->save("some_id", 123);
        $this->assertSame(123, $session->get("some_id"));
        $this->assertNull($session->get("doesnt_exist"));
        $this->assertSame(50, $session->get("doesnt_exist_2", 50));
    }

    /**
     * @runInSeparateProcess
     */
    public function testDelete()
    {
        $session = Session::getInstance();;
        $session->save("some_id", 123);
        $this->assertSame(123, $session->get("some_id"));
        $result = $session->delete("some_id");
        $this->assertTrue($result);
        $this->assertNull($session->get("some_id"));
    }

    /**
     * @runInSeparateProcess
     */
    public function getAll()
    {
        $session = Session::getInstance();;
        $session->save("some_id", 123);
        $this->assertSame(["some_id" => 123], $session->getAll());
    }

    /**
     * @runInSeparateProcess
     */
    public function testGetSessionId()
    {
        $session = Session::getInstance();;
        $this->assertSame(session_id(), $session->getSessionId());
    }
}