<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Image\Tests\Bridge;

use Webiny\Component\Config\ConfigObject;
use Webiny\Component\Image\Bridge\ImageLoaderInterface;
use Webiny\Component\Image\Bridge\Loader;

class LoaderTest extends \PHPUnit_Framework_TestCase
{

    public function testGetImageLoader()
    {
        $config = new ConfigObject([]);
        $loader = Loader::getImageLoader($config);
        $this->assertInstanceOf(ImageLoaderInterface::class, $loader);
    }

    /**
     * @expectedException
     */
    public function testGetImageLoaderException()
    {
        $config = new ConfigObject(['Bridge' => 'doesnt exist']);
        Loader::getImageLoader($config);
    }
}