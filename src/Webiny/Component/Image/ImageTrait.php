<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Image;

use Webiny\Component\ServiceManager\ServiceManager;
use Webiny\Component\Storage\File\File;
use Webiny\Component\Storage\Storage;

/**
 * ImageTrait provides easier access to loading images and creating Image Instances.
 *
 * @package         Webiny\Component\Image
 */
trait ImageTrait
{

    /**
     * Loads the image and returns an instance of Image class.
     *
     * @param string|File    $image             This can either be image file name that corresponds to File $key parameter,
     *                                          or it can be an instance of Webiny\Component\Storage\File\File.
     * @param string|Storage $storage           This can either be the name of the storage service or it can be an
     *                                          instance of Webiny\Component\Storage\Storage.
     *                                          NOTE: This parameter is not required if you pass the $image as
     *                                          instance of Webiny\Component\Storage\File\File.
     *
     * @return ImageInterface
     */
    public function image($image, $storage = 'local')
    {
        if ($image instanceof File) {
            return ImageLoader::load($image->getContents());
        } else {
            if (!($storage instanceof Storage)) {
                $storage = ServiceManager::getInstance()->getService('Storage.' . $storage);
            }

            $file = new File($image, $storage);

            return ImageLoader::load($file->getContents());
        }
    }
}