<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Image\Bridge;

use Webiny\Component\Config\ConfigObject;
use Webiny\Component\Image\Bridge\Imagine\Imagine;
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
    private static $library = Imagine::class;

    /**
     * Returns an instance of ImageLoaderInterface based on current bridge.
     *
     * @param ConfigObject $config
     *
     * @throws ImageException
     *
     * @return \Webiny\Component\Image\Bridge\ImageLoaderInterface
     */
    public static function getImageLoader(ConfigObject $config)
    {
        $lib = self::getLibrary();

        /** @var ImageLoaderInterface $libInstance */
        $instance = self::factory($lib, ImageLoaderInterface::class, [$config]);

        if (!self::isInstanceOf($instance, ImageLoaderInterface::class)) {
            throw new ImageException('The message library must implement "' . ImageLoaderInterface::class . '".');
        }

        return $instance;
    }

    /**
     * Get the name of bridge library which will be used as the driver.
     *
     * @return string
     */
    private static function getLibrary()
    {
        return Image::getConfig()->get('Bridge', self::$library);
    }

    /**
     * Change the default library used for the driver.
     *
     * @param string $pathToClass Path to the new driver class. Must be an instance of \Webiny\Component\Image\Bridge\ImageLoaderInterface
     */
    public static function setLibrary($pathToClass)
    {
        self::$library = $pathToClass;
    }
}