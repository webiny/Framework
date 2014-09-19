<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Http\Tests\Request;


use Webiny\Component\Http\Http;
use Webiny\Component\Http\Request\Post;

class PostTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Http::setConfig(__DIR__ . '/../ExampleConfig.yaml');
    }

    public function testConstructor()
    {
        $post = new Post();
        $this->assertInstanceOf('\Webiny\Component\Http\Request\Post', $post);
    }

    public function testGet()
    {
        $_POST = ["name" => "jack"];
        $post = new Post();

        $this->assertSame("jack", $post->get("name"));
        $this->assertNull($post->get("NON_EXISTING"));
        $this->assertSame("doe", $post->get("NON_EXISTING_2", "doe"));
    }

    public function testGetAll()
    {
        $_POST = [
            "name"    => "jack",
            "surname" => "doe"
        ];

        $post = new Post();
        $this->assertSame($_POST, $post->getAll());
    }
}