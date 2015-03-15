<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Image\Bridge\Imagine;

use Imagine\Image\Box;
use Webiny\Component\Image\Bridge\ImageLoaderInterface;
use Webiny\Component\Config\ConfigObject;
use Webiny\Component\StdLib\StdLibTrait;
use Webiny\Component\Storage\File\LocalFile;

/**
 * This class is the main bridge to Imagine library.
 *
 * @package         Webiny\Component\Image\Bridge\Imagine
 */
class Imagine implements ImageLoaderInterface
{
    use StdLibTrait;

    /**
     * @var \Imagine\Gd\Imagine|\Imagine\Gmagick\Imagine|\Imagine\Imagick\Imagine
     */
    private $instance;


    /**
     * Base constructor.
     *
     * @param ConfigObject $config
     */
    public function __construct(ConfigObject $config)
    {
        $library = $this->str($config->get('Library', 'gd'))->caseLower()->val();
        $this->instance = $this->getLibraryInstance($library);
    }

    /**
     * Create a library instance based on given library name.
     *
     * @param string $library Name of the library. Supported libraries are gd, imagick and gmagick.
     *
     * @return \Imagine\Gd\Imagine|\Imagine\Gmagick\Imagine|\Imagine\Imagick\Imagine
     * @throws ImagineException
     */
    private function getLibraryInstance($library)
    {
        switch ($library) {
            case 'gd':
                return new \Imagine\Gd\Imagine();
                break;

            case 'imagick':
                return new \Imagine\Imagick\Imagine();
                break;

            case 'gmagick':
                return new \Imagine\Gmagick\Imagine();
                break;

            default:
                throw new ImagineException('Unsupported image library "' . $library . '". Cannot create Imagine instance.'
                );
                break;
        }
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
     * @param int $alpha Alpha transparency.
     *
     * @return \Webiny\Component\Image\ImageInterface
     */
    public function create($width, $height, $bgColor = null, $alpha = 100)
    {
        $size = new Box($width, $height);
        $palette = new \Imagine\Image\Palette\RGB();
        $color = $palette->color($bgColor, $alpha);


        return new Image($this->instance->create($size, $color));
    }

    /**
     * Creates a new ImageInterface instance from the given image at the provided path.
     *
     * @param LocalFile $image Path to an image on the disk.
     *
     * @return \Webiny\Component\Image\ImageInterface
     */
    public function open(LocalFile $image)
    {
        return new Image($this->instance->open($image->getAbsolutePath()));
    }

    /**
     * Create a new ImageInterface instance form the given binary string.
     *
     * @param string $string Binary string that holds image information.
     *
     * @return \Webiny\Component\Image\ImageInterface
     */
    public function load($string)
    {
        return new Image($this->instance->load($string));
    }

    /**
     * Create a new ImageInterface instance from the given resource.
     *
     * @param mixed $resource Resource.
     *
     * @return \Webiny\Component\Image\ImageInterface
     */
    public function resource($resource)
    {
        return new Image($this->instance->read($resource));
    }
}