<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Image;

use Webiny\Component\Image\Bridge\ImageLoaderInterface;
use Webiny\Component\Image\Bridge\Loader;
use Webiny\Component\StdLib\StdLibTrait;
use Webiny\Component\Storage\File\LocalFile;

/**
 * Use this class to create an Image instance.
 * You can load images using these methods:
 *  - `open` => opens an image from disk by providing an instance of \Webiny\Component\Storage\File\File
 *  - `load` => creates an image from given binary string
 *  - `create` => creates a blank image
 *  - `resource` => create an image from the given resource, e.g. from upload stream
 *
 * @package         Webiny\Component\Image
 */
class ImageLoader
{
    use StdLibTrait;

    /**
     * @var null|ImageLoaderInterface
     */
    private static $_loader = null;

    /**
     * Returns an instance of ImageLoaderInterface.
     *
     * @return null|ImageLoaderInterface
     */
    private static function _getLoader()
    {
        if (self::isNull(self::$_loader)) {
            self::$_loader = Loader::getImageLoader(Image::getConfig());
        }

        return self::$_loader;
    }

    /**
     * Create a blank image with of given dimensions and fill it with $bgColor.
     *
     * @param int    $width       Width of the new image.
     * @param int    $height      Height of the new image.
     * @param string $bgColor     Background color. Following formats are acceptable
     *                            - "fff"
     *                            - "ffffff"
     *                            - array(255,255,255)
     *
     * @return ImageInterface
     */
    public static function create($width, $height, $bgColor = null)
    {
        return self::_getLoader()->create($width, $height, $bgColor);
    }

    /**
     * Create a new ImageInterface instance form the given binary string.
     *
     * @param string $string Binary string that holds image information.
     *
     * @return mixed
     */
    public static function load($string)
    {
        return self::_getLoader()->load($string);
    }

    /**
     * Create a new ImageInterface instance from the given resource.
     *
     * @param mixed $resource Resource.
     *
     * @return ImageInterface
     */
    public static function resource($resource)
    {
        return self::_getLoader()->resource($resource);
    }

    /**
     * Creates a new ImageInterface instance from the given image at the provided path.
     *
     * @param LocalFile $image Path to an image on the disk.
     *
     * @return ImageInterface
     */
    public static function open(LocalFile $image)
    {
        $img = self::_getLoader()->open($image);
        $img->setDestination($image);

        // extract the format
        $format = self::str($image->getKey())->explode('.')->last()->caseLower()->val();
        $img->setFormat($format);

        return $img;
    }
}