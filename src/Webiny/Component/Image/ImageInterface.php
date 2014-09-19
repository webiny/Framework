<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Image;

use \Webiny\Component\Storage\File\File;

/**
 * ImageInterface must be implemented by each image bridge library.
 *
 * @package         Webiny\Component\Image
 */
interface ImageInterface
{

    /**
     * Returns the width and height of the image in pixels.
     *
     * @return ArrayObject
     */
    public function getSize();

    /**
     * Get image as a binary string.
     *
     * @param array $options List of additional options. Possible values are [quality].
     *
     * @return string
     */
    public function getBinary($options = []);

    /**
     * Get the image mime-type format.
     * Can be [jpg, jpeg, png, gif].
     *
     * @return string
     */
    public function getFormat();

    /**
     * Sets image mime-type format.
     *
     * @param string $format Format name. Supported formats are [jpg, jpeg, png, gif]
     *
     * @throws ImageException
     */
    public function setFormat($format);

    /**
     * Sets the image destination.
     *
     * @param File $destination Destination where to store the image.
     */
    public function setDestination(File $destination);

    /**
     * Crop the image to the given dimensions.
     *
     * @param int $width   Width on the new image.
     * @param int $height  Height of the new image.
     * @param int $offestX Crop start position on X axis.
     * @param int $offestY Crop start position on Y axis.
     *
     * @return ImageInterface
     */
    public function crop($width, $height, $offestX = 0, $offestY = 0);

    /**
     * Resize the image to given dimensions.
     *
     * @param int  $width                         Width of the new image.
     * @param int  $height                        Height of the new image.
     * @param bool $preserveAspectRatio           Do you wish to preserve the aspect ration while resizing. Default is true.
     *                                            NOTE: If you preserve the aspect ratio, the output image might not match the
     *                                            defined width and height.
     *
     * @return ImageInterface
     */
    public function resize($width, $height, $preserveAspectRatio = true);

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
     * @return ImageInterface
     */
    public function rotate($angle, $bgColor = null);

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
    public function thumbnail($width, $height, $cropOrPad = false, $padColor = null);

    /**
     * Saves the current image to the given location.
     *
     * @param \Webiny\Component\Storage\File\File $file
     * @param array                               $options An array of options. Possible keys are [quality, filters].
     *
     * @internal param \Webiny\Component\Storage\File\File $fileName
     *
     * @return $this
     */
    public function save(File $file = null, $options = []);

    /**
     * Output the image into the browser.
     *
     * @return string
     */
    public function show();

    /**
     * Paste another image into this one a the specified dimension.
     *
     * @param ImageInterface $image   Image to paste.
     * @param int            $offsetX Offset on x axis.
     * @param int            $offsetY Offset on y axis
     *
     * @return $this
     */
    public function paste(ImageInterface $image, $offsetX = 0, $offsetY = 0);

    /**
     * This method returns the instance of the Image object from the bridged library.
     * The usage of this method is discouraged, but it's necessary for some internal operations.
     *
     * @return mixed
     */
    public function getInstance();
}