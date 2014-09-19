<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Image\Tests\Bridge\Imagine;

use Webiny\Component\Image\Image;
use Webiny\Component\Image\ImageLoader;

class ImageTest extends \PHPUnit_Framework_TestCase
{
    const CONFIG = 'ExampleConfig.yaml';

    /**
     * @dataProvider provideImage
     */
    public function testConstructor($image)
    {
        $this->assertInstanceOf('\Webiny\Component\Image\Bridge\Imagine\Image', $image);
    }

    /**
     * @dataProvider provideImage
     */
    public function testGetBinary($image)
    {
        $this->assertNotEmpty($image->getBinary());
    }

    /**
     * @dataProvider provideImage
     */
    public function testGetSize($image)
    {
        $size = $image->getSize()->val();
        $this->assertSame([
                              'width'  => 1,
                              'height' => 1
                          ], $size
        );
    }

    public function provideImage()
    {
        Image::setConfig(realpath(__DIR__ . '/../../' . self::CONFIG));

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
        $file->expects($this->once())->method('getAbsolutePath')->will($this->returnValue(__DIR__ . '/../../image.gif')
        );

        // getKey mock
        $file->expects($this->once())->method('getKey')->will($this->returnValue(__DIR__ . '/../../image.gif'));

        $image = ImageLoader::open($file);

        return [[$image]];
    }
}