<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Image\Tests;


use Webiny\Component\Config\ConfigObject;
use Webiny\Component\Image\Bridge\Imagine\Imagine;
use Webiny\Component\Image\Image;

class ImageTest extends \PHPUnit_Framework_TestCase
{
    const CONFIG = '/ExampleConfig.yaml';

    public function setUp()
    {
        Image::setConfig(realpath(__DIR__ . self::CONFIG));
    }

    public function testGetConfig()
    {
        $this->assertInstanceOf(ConfigObject::class, Image::getConfig());
    }

    public function testConfigContent()
    {
        $this->assertSame(90, Image::getConfig()->Quality);
        $this->assertSame('gd', Image::getConfig()->Library);
        $this->assertSame('\\' . Imagine::class, Image::getConfig()->Bridge);
    }
}