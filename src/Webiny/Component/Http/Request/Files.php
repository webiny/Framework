<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Http\Request;

use Webiny\Component\Http\Request\Files\File;
use Webiny\Component\Http\Request\Files\FilesException;
use Webiny\Component\StdLib\StdLibTrait;

/**
 * File Http component.
 *
 * @package         Webiny\Component\Http
 */
class Files
{
    use StdLibTrait;

    private $fileBag;
    private $fileObject;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->fileBag = $this->arr($_FILES);
    }

    /**
     * Get the File object for the given $name.
     * If you have a multi-dimensional upload field name, than you should pass the optional $arrayOffset
     * param to get the right File object.
     *
     * @param string   $name        Name of the upload field.
     * @param null|int $arrayOffset Optional array offset for multi-dimensional upload fields.
     *
     * @throws Files\FilesException
     * @return File
     */
    public function get($name, $arrayOffset = null)
    {
        // first some validations
        if (!$this->fileBag->keyExists($name)) {
            throw new FilesException('Upload field with name "' . $name . '" was not found in the $_FILES array.');
        }

        // check to see if we have already created the file object
        if (isset($this->fileObject[$name])) {
            $fileObject = $this->getFileObject($name, $arrayOffset);
            if ($fileObject) {
                return $fileObject;
            }
        }

        // create and return File object
        $file = $this->fileBag->key($name);
        if (is_null($arrayOffset)) {
            $fileObject = $this->createFileObject($file, $arrayOffset);

            return $fileObject;
        } else {
            if (!isset($file['name'][$arrayOffset])) {
                throw new FilesException('Uploaded file with name "' . $name . '" and
											offset "' . $arrayOffset . '" was not found in the $_FILES array.'
                );
            }

            $fileObject = $this->createFileObject($file, $arrayOffset);

            return $fileObject;
        }
    }

    /**
     * Create the File object.
     *
     * @param array $file
     * @param null  $arrayOffset
     *
     * @return File
     */
    private function createFileObject($file, $arrayOffset = null)
    {
        if (!is_null($arrayOffset)) {
            $fileObject = new File($file['name'][$arrayOffset], $file['tmp_name'][$arrayOffset],
                                   $file['type'][$arrayOffset], $file['error'][$arrayOffset],
                                   $file['size'][$arrayOffset]
            );
            $this->fileObject[$file['name']][$arrayOffset] = $fileObject;
        } else {
            $fileObject = new File($file['name'], $file['tmp_name'], $file['type'], $file['error'], $file['size']);
            $this->fileObject[$file['name']] = $fileObject;
        }

        return $fileObject;
    }

    /**
     * Check if we have already create a File object of the given $name.
     *
     * @param  string $name
     * @param null    $arrayOffset
     *
     * @return bool|File False if the object is not created, otherwise File is returned.
     */
    private function getFileObject($name, $arrayOffset = null)
    {
        if (!is_null($arrayOffset)) {
            if (isset($this->fileObject[$name]) && $this->isArray($this->fileObject[$name]
                ) && isset($this->fileObject[$name][$arrayOffset]) && $this->isInstanceOf($this->fileObject[$name][$arrayOffset],
                                                                                           'Webiny\Component\Http\Request\Files\File'
                )
            ) {
                return $this->fileObject[$name][$arrayOffset];
            }
        } else {
            if (isset($this->fileObject[$name]) && $this->isInstanceOf($this->fileObject[$name],
                                                                        'Webiny\Component\Http\Request\Files\File'
                )
            ) {
                return $this->fileObject[$name];
            }
        }

        return false;
    }
}