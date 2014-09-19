<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Http\Tests\Request\Files;


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
                "tmp_name" => "webiny_logo.jpg",
                "size"     => "2048",
                "error"    => "0"
            ]
        ];

        Http::setConfig(__DIR__ . '/../../ExampleConfig.yaml');
    }

    public function testGetName()
    {
        $files = new Files();
        $testFile = $files->get("test_file");

        $this->assertSame("some_image.jpg", $testFile->getName());
    }

    public function testGetType()
    {
        $files = new Files();
        $testFile = $files->get("test_file");


        $this->assertSame("image/jpeg", $testFile->getType());
    }

    public function testGetTmpName()
    {
        $files = new Files();
        $testFile = $files->get("test_file");


        $this->assertSame("webiny_logo.jpg", $testFile->getTmpName());
    }

    public function testGetError()
    {
        $files = new Files();
        $testFile = $files->get("test_file");


        $this->assertSame("0", $testFile->getError());
    }

    public function testGetSize()
    {
        $files = new Files();
        $testFile = $files->get("test_file");

        $this->assertSame("2048", $testFile->getSize());
    }

    /*function testStore()
    {
        $files = new Files();
        $testFile = $files->get("test_file");

        $result = $testFile->store(__DIR__, "webiny_logo_stored.jpg");

        $this->assertTrue($result);
    }*/
}