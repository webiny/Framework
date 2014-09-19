<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Http\Request\Files;

use Webiny\Component\StdLib\StdLibTrait;

/**
 * File wrapper for Http component.
 *
 * @package         Webiny\Component\Http\Request\Files
 */
class File
{

    use StdLibTrait;

    private $_name;
    private $_tmpName;
    private $_type;
    private $_error;
    private $_size;
    private $_stored = false;
    private $_storedPath = '';

    /**
     * Constructor.
     *
     * @param string $name    Original name of the uploaded file.
     * @param string $tmpName Temp file name.
     * @param string $type    File mime-type.
     * @param int    $error   Error code, 0 if there is no error.
     * @param int    $size    Size of the file, in bytes.
     */
    public function __construct($name, $tmpName, $type, $error, $size)
    {
        $this->_name = $name;
        $this->_tmpName = $tmpName;
        $this->_type = $type;
        $this->_error = $error;
        $this->_size = $size;
    }

    /**
     * Get the original file name.
     *
     * @return string Original file name.
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Get the location and name of the uploaded file on the server.
     *
     * @return string Temp location of the uploaded file on the server.
     */
    public function getTmpName()
    {
        return $this->_tmpName;
    }

    /**
     * Returns mime-type of the uploaded file.
     *
     * @return string File mime-type.
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Get upload error code.
     *
     * @return int Error code.
     */
    public function getError()
    {
        return $this->_error;
    }

    /**
     * Get the size of uploaded file, in bytes.
     *
     * @return int File size in bytes.
     */
    public function getSize()
    {
        return $this->_size;
    }

    /**
     * Store the uploaded file to a designated destination.
     *
     * @param string      $folder   Folder under which the file will be saved.
     * @param null|string $filename If you wish to store the file under a different name, other than the original uploaded name.
     *
     * @return bool True if file was successfully saved in the designated destination.
     * @throws FilesException
     */
    public function store($folder, $filename = null)
    {
        // check for errors
        if ($this->getError() == UPLOAD_ERR_OK) {
            throw new FilesException('Unable to move the file because an upload error occurred.');
        }

        // validate filename
        $filename = isset($filename) ? $filename : $this->getName();

        // validate folder
        $folder = $this->str($folder);
        if (!$folder->endsWith('/') && !$folder->endsWith('\\')) {
            $folder = $folder->val() . DIRECTORY_SEPARATOR;
        } else {
            $folder->val();
        }

        // check if we have already stored the file
        $path = $folder . $filename;
        if ($this->_stored && $this->_storedPath == $path) {
            return true;
        }

        // move the file
        try {
            $result = move_uploaded_file($this->_tmpName, $path);
            if (!$result) {
                throw new FilesException('Unable to store file.');
            }
            $this->_stored = $result;
            $this->_storedPath = $folder . $filename;
        } catch (\Exception $e) {
            throw new FilesException($e->getMessage());
        }

        return $result;
    }
}