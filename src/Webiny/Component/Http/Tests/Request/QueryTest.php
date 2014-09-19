<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Http\Tests\Request;


use Webiny\Component\Http\Http;
use Webiny\Component\Http\Request\Query;

class QueryTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Http::setConfig(__DIR__ . '/../ExampleConfig.yaml');
    }

    public function testConstructor()
    {
        $query = new Query();
        $this->assertInstanceOf('\Webiny\Component\Http\Request\Query', $query);
    }

    public function testGet()
    {
        $_GET = ["name" => "jack"];
        $query = new Query();

        $this->assertSame("jack", $query->get("name"));
        $this->assertNull($query->get("NON_EXISTING"));
        $this->assertSame("doe", $query->get("NON_EXISTING_2", "doe"));
    }

    public function testGetAll()
    {
        $_GET = [
            "name"    => "jack",
            "surname" => "doe"
        ];

        $query = new Query();
        $this->assertSame($_POST, $query->getAll());
    }
}