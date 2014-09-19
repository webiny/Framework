<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Image\Bridge;

use Webiny\Component\Config\ConfigObject;
use Webiny\Component\Image\Image;
use Webiny\Component\StdLib\FactoryLoaderTrait;
use Webiny\Component\StdLib\StdLibTrait;


/**
 * Image bridge loader.
 *
 * @package         Webiny\Component\Image\Bridge
 */
class Loader
{
    use FactoryLoaderTrait, StdLibTrait;

    /**
     * @var string Default Image bridge.
     */
    private static $_library = '\Webiny\Component\Image\Bridge\Imagine\Imagine';


    /**
     * Returns an instance of ImageLoaderInterface based on current bridge.
     *
     * @param ConfigObject $config
     *
     * @throws ImageException
     *
     * @return \Webiny\Component\Image\ImageLoaderInterface
     */
    public static function getImageLoader(ConfigObject $config)
    {
        $lib = self::_getLibrary();

        /** @var ImageLoaderInterface $libInstance */
        $instance = self::factory($lib, '\Webiny\Component\Image\Bridge\ImageLoaderInterface', [$config]);

        if (!self::isInstanceOf($instance, '\Webiny\Component\Image\Bridge\ImageLoaderInterface')) {
            throw new ImageException('The message library must implement "\Webiny\Component\Image\Bridge\ImageLoaderInterface".'
            );
        }

        return $instance;
    }


    /**
     * Get the name of bridge library which will be used as the driver.
     *
     * @return string
     */
    private static function _getLibrary()
    {
        return Image::getConfig()->get('Bridge', self::$_library);
    }

    /**
     * Change the default library used for the driver.
     *
     * @param string $pathToClass Path to the new driver class. Must be an instance of \Webiny\Component\Image\Bridge\ImageLoaderInterface
     */
    public static function setLibrary($pathToClass)
    {
        self::$_library = $pathToClass;
    }
}