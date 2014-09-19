<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Image\Bridge\Imagine;

use Imagine\Image\Box;
use Imagine\Image\Color;
use Imagine\Image\Point;
use Webiny\Component\Image\Bridge\ImageAbstract;
use Webiny\Component\Image\ArrayObject;
use Webiny\Component\Image\ImageInterface;
use Webiny\Component\Image\ImageLoader;
use Webiny\Component\StdLib\StdLibTrait;

/**
 * This is the image manipulation class for Imagine library.
 *
 * @package         Webiny\Component\Image\Bridge\Imagine
 */
class Image extends ImageAbstract
{
    use StdLibTrait;

    const CROP = 'crop';
    const PAD = 'pad';
    const QUALITY = 90;

    /**
     * @var \Imagine\Imagick\Image
     */
    private $_image;

    /**
     * Base constructor.
     *
     * @param \Imagine\Image\ImageInterface $image
     */
    public function __construct(\Imagine\Image\ImageInterface $image)
    {
        $this->_image = $image;
    }

    /**
     * Get image as a binary string.
     *
     * @param array $options An array of options. Possible keys are [quality, filters].
     *
     * @return string
     */
    public function getBinary($options = [])
    {
        $options['quality'] = isset($options['quality']) ? $options['quality'] : self::QUALITY;

        return $this->_image->get($this->getFormat(), $options);
    }

    /**
     * Returns the width and height of the image in pixels.
     *
     * @return ArrayObject
     */
    public function getSize()
    {
        $size = $this->_image->getSize();

        return $this->arr([
                              'width'  => $size->getWidth(),
                              'height' => $size->getHeight()
                          ]
        );
    }

    /**
     * Crop the image to the given dimensions.
     *
     * @param int $width   Width on the new image.
     * @param int $height  Height of the new image.
     * @param int $offestX Crop start position on X axis.
     * @param int $offestY Crop start position on Y axis.
     *
     * @return $this
     */
    public function crop($width, $height, $offestX = 0, $offestY = 0)
    {
        $pointer = new Point($offestX, $offestY);
        $size = new Box($width, $height);

        $this->_image->crop($pointer, $size);

        return $this;
    }

    /**
     * Resize the image to given dimensions.
     *
     * @param int  $width                         Width of the new image.
     * @param int  $height                        Height of the new image.
     * @param bool $preserveAspectRatio           Do you wish to preserve the aspect ration while resizing. Default is true.
     *                                            NOTE: If you preserve the aspect ratio, the output image might not match the
     *                                            defined width and height.
     *
     * @return $this
     */
    public function resize($width, $height, $preserveAspectRatio = true)
    {
        if ($preserveAspectRatio) {
            $currentSize = $this->getSize();

            $currentAspectRatio = round($currentSize->width / $currentSize->height, 3);
            $aspectRatio = round($width / $height, 3);

            if ($currentAspectRatio <> $aspectRatio) {
                if ($width < $height) {
                    $height = round($width / $currentAspectRatio);
                } else {
                    $width = round($height * $currentAspectRatio);
                }
            }
        }

        $size = new Box($width, $height);

        $this->_image->resize($size);

        return $this;
    }

    /**
     * Rotate the image under the given $angle.
     *
     * @param int         $angle   Angle in degrees how much to rotate the image.
     * @param null|string $bgColor Optional parameter that fills the background with the defined color.
     *                             Following formats are acceptable
     *                             - "fff"
     *                             - "ffffff"
     *                             - array(255,255,255)
     *
     * @return $this
     */
    public function rotate($angle, $bgColor = null)
    {
        $color = new Color($bgColor);

        $this->_image->rotate($angle, $color);

        return $this;
    }

    /**
     * Output the image into the browser.
     *
     * @return string
     */
    public function show()
    {
        $this->_image->show($this->getFormat());
    }

    /**
     * This is a method that combines resize, crop and paste methods in order to generate a thumbnail from the given image.
     * The benefit of using this function is that the function can automatically combine crop and resize methods together
     * with the pad feature in order to generate the thumb.
     *
     * @param int         $width     Thumb width.
     * @param int         $height    Thumb height.
     * @param bool|string $cropOrPad If you set this to 'crop' the method will first resize the image to preserve the
     *                               aspect ratio and then it will crop the extra pixels to fit the defined width and height.
     *                               If you set this to 'pad' the method will first do the resize and than
     *                               it wil create a blank image that has the size of defined width and height and fill it
     *                               with $padColor, then it will paste the resized image in the center of the new image.
     * @param null|string $padColor  Parameter that fills the background with the defined color.
     *                               Following formats are acceptable
     *                               - "fff"
     *                               - "ffffff"
     *                               - array(255,255,255)
     *
     * @return $this
     */
    public function thumbnail($width, $height, $cropOrPad = false, $padColor = null)
    {
        // get the aspect ratio
        $currentSize = $this->getSize();
        $ar = round($currentSize['width'] / $currentSize['height'], 3);
        $nar = round($width / $height, 3);

        $newWidth = $width;
        $newHeight = $height;
        if ($ar >= 1) {
            if ($nar > $ar) {
                $newHeight = $width / $ar;
            } else {
                $newWidth = $height * $ar;
            }
        } else {
            if ($nar > $ar) {
                $newHeight = $width / $ar;
            } else {
                $newWidth = $height * $ar;
            }
        }
        $this->resize($newWidth, $newHeight);

        // crop
        if ($cropOrPad == self::CROP) {
            $this->crop($width, $height);
        }

        // pad
        if ($cropOrPad == self::PAD) {
            $padColor = !empty($padColor) ? $padColor : 'ffffff';
            $image = ImageLoader::create($width, $height, $padColor);

            // re-calculate the size based on aspect ratio
            if ($width < $height) {
                $newWidth = $width;
                $newHeight = round($width / $ar, 0);
            } else {
                $newWidth = round($height / $ar, 0);
                $newHeight = $height;
            }

            // center the padded image
            $offsetX = ($width - $newWidth) / 2;
            $offsetY = ($height - $newHeight) / 2;

            // resize the current image
            $this->resize($newWidth, $newHeight);

            $image->paste($this, $offsetX, $offsetY);
            $this->_image = $image->getInstance();
            unset($image);
        }

        return $this;
    }

    /**
     * Paste another image into this one a the specified dimension.
     *
     * @param ImageInterface $image   Image to paste.
     * @param int            $offsetX Offset on x axis.
     * @param int            $offsetY Offset on y axis
     *
     * @return $this
     */
    public function paste(ImageInterface $image, $offsetX = 0, $offsetY = 0)
    {
        $point = new Point($offsetX, $offsetY);

        $this->_image->paste($image->getInstance(), $point);

        return $this;
    }

    /**
     * This method returns the instance of the Image object from the bridged library.
     * The usage of this method is discouraged, but it's necessary for some internal operations.
     *
     * @return mixed
     */
    public function getInstance()
    {
        return $this->_image;
    }
}