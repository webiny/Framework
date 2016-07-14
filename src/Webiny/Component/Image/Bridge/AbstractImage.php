<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Image\Bridge;

use Webiny\Component\Image\Image;
use Webiny\Component\StdLib\StdLibTrait;
use Webiny\Component\Storage\File\File;


/**
 * Image abstract class.
 *
 * @package         Webiny\Component\Image\Bridge
 */
abstract class AbstractImage implements ImageInterface
{
    use StdLibTrait;

    /**
     * @var string File format
     */
    private $format = 'png';

    /**
     * @var File
     */
    private $destination;

    /**
     * @var array
     */
    private static $formats = [
        'jpg',
        'jpeg',
        'png',
        'gif'
    ];


    /**
     * Get the image mime-type format.
     * Can be [jpg, jpeg, png, gif].
     *
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * Sets image mime-type format.
     *
     * @param string $format Format name. Supported formats are [jpg, jpeg, png, gif]
     *
     * @throws ImageException
     */
    public function setFormat($format)
    {
        if (!in_array($format, self::$formats)) {
            throw new ImageException('Invalid image format provided. Supported formats are [jpg, jpeg, png, gif].');
        }

        $this->format = $format;
    }

    /**
     * Sets the image destination.
     *
     * @param File $destination Destination where to store the image.
     */
    public function setDestination(File $destination)
    {
        $this->destination = $destination;
    }

    /**
     * Saves the image in the defined storage.
     *
     * @param File  $file    Where to save the image.
     * @param array $options An array of options. Possible keys are [quality, filters].
     *
     * @return bool True if image is save successfully, otherwise false.
     *
     * @throws \Exception|ImageException
     */
    public function save(File $file = null, $options = [])
    {
        if ($this->isNull($file)) {
            if ($this->isNull($this->destination)) {
                throw new ImageException('Unable to save the image. Destination storage is not defined.');
            }

            $file = $this->destination;
        }

        // extract the type
        try {
            $format = $this->str($file->getKey())->explode('.')->last()->caseLower()->val();
            $this->setFormat($format);
        } catch (ImageException $e) {
            throw $e;
        }

        // check quality parameter
        $options['quality'] = isset($options['Quality']) ? $options['Quality'] : Image::getConfig()->get('Quality', 90);

        return $file->setContents($this->getBinary($options));
    }
}