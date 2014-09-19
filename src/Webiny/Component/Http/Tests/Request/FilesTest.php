<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Http\Tests\Request;


use Webiny\Component\Http\Http;
use Webiny\Component\Http\Request\Files;

class FilesTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
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

        Http::setConfig(__DIR__ . '/../ExampleConfig.yaml');
    }

    public function testConstructor()
    {
        $files = new Files();
        $this->assertInstanceOf('\Webiny\Component\Http\Request\Files', $files);
    }

    public function testGet()
    {
        $files = new Files();
        $testFile = $files->get("test_file");

        $this->assertInstanceOf('\Webiny\Component\Http\Request\Files\File', $testFile);
    }


    /**
     * @expectedException \Webiny\Component\Http\Request\Files\FilesException
     */
    public function testGetException()
    {
        $files = new Files();
        $files->get("doesnt_exit");
    }
}