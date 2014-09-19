<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Image\Tests;


use Webiny\Component\Image\Image;
use Webiny\Component\Image\ImageLoader;
use Webiny\Component\Storage\StorageTrait;

class ImageLoaderTest extends \PHPUnit_Framework_TestCase
{
    use StorageTrait;

    const CONFIG = '/ExampleConfig.yaml';

    public function setUp()
    {
        Image::setConfig(realpath(__DIR__ . self::CONFIG));
    }

    public function testCreate()
    {
        $image = ImageLoader::create(1, 1, '#666666');

        $this->assertInstanceOf('\Webiny\Component\Image\ImageInterface', $image);
    }

    public function testLoad()
    {
        $image = ImageLoader::load(base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'));

        $this->assertInstanceOf('\Webiny\Component\Image\ImageInterface', $image);
    }

    public function testResource()
    {
        $stream = fopen('data://text/plain;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7', 'r');
        $image = ImageLoader::resource($stream);

        $this->assertInstanceOf('\Webiny\Component\Image\ImageInterface', $image);
    }

    public function testOpen()
    {
        // build LocalFile mock
        $file = $this->getMockBuilder('\Webiny\Component\Storage\File\LocalFile')
                     ->disableOriginalConstructor()
                     ->setMethods([
                                      'getAbsolutePath',
                                      'getKey'
                                  ]
                     )
                     ->getMock();

        // getAbsolutePath mock
        $file->expects($this->once())->method('getAbsolutePath')->will($this->returnValue(__DIR__ . '/image.gif'));

        // getKey mock
        $file->expects($this->once())->method('getKey')->will($this->returnValue(__DIR__ . '/image.gif'));

        $image = ImageLoader::open($file);
        $this->assertInstanceOf('\Webiny\Component\Image\ImageInterface', $image);
    }

}