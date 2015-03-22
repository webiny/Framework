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
use Webiny\Component\Storage\File\File;

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
    private static $loader = null;

    /**
     * Returns an instance of ImageLoaderInterface.
     *
     * @return null|ImageLoaderInterface
     */
    private static function getLoader()
    {
        if (self::isNull(self::$loader)) {
            self::$loader = Loader::getImageLoader(Image::getConfig());
        }

        return self::$loader;
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
        return self::getLoader()->create($width, $height, $bgColor);
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
        return self::getLoader()->load($string);
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
        return self::getLoader()->resource($resource);
    }

    /**
     * Creates a new ImageInterface instance from the given image at the provided path.
     *
     * @param File $image Path to an image on the disk.
     *
     * @return ImageInterface
     */
    public static function open(File $image)
    {
        $img = self::getLoader()->open($image);
        $img->setDestination($image);

        // extract the format
        $format = self::str($image->getKey())->explode('.')->last()->caseLower()->val();
        $img->setFormat($format);

        // fix image orientation (iPhone/Android issue)
        self::fixImageOrientation($image, $img);

        return $img;
    }

    /**
     * Android and Iphone images are sometimes rotated "incorrectly".
     * This method fixes that.
     * Method is called automatically on the `open` method.
     *
     * @param File      $imageFile
     * @param ImageInterface $image
     */
    private static function fixImageOrientation(File $imageFile, ImageInterface $image)
    {
        $format = $image->getFormat();

        // exif data is available only on jpeg and tiff
        // tiff is ignored, because smartphones don't produce tiff images
        if ($format == 'jpg' || $format == 'jpeg') {
            $exifData = exif_read_data($imageFile->getAbsolutePath(), 'IFDO');
            if (isset($exifData['Orientation'])) {
                switch ($exifData['Orientation']) {
                    case 3:
                        $rotation = 180;
                        break;
                    case 6:
                        $rotation = 90;
                        break;
                    case 8:
                        $rotation = -90;
                        break;
                    default:
                        $rotation = 0;
                        break;
                }

                if($rotation!=0){
                    $image->rotate($rotation);
                }
            }
        }
    }
}