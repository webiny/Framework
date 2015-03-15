<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Storage\File;

use Webiny\Component\StdLib\StdObject\DateTimeObject\DateTimeObject;
use Webiny\Component\StdLib\StdObjectTrait;

/**
 * LocalFile is used for local disk storage
 *
 * @package  Webiny\Component\Storage\File
 */
class LocalFile extends File
{
    use StdObjectTrait;

    protected $size;

    /**
     * Get file size in bytes
     *
     * @return int|boolean Number of bytes or false
     */
    public function getSize()
    {
        if ($this->size === null) {
            $this->size = $this->storage->getSize($this->key);
        }

        return $this->size;
    }

    /**
     * Get absolute file path
     *
     * @return string
     */
    public function getAbsolutePath()
    {
        return $this->storage->getAbsolutePath($this->key);
    }

    /**
     * Touch a file (change time modified)
     *
     * @return $this
     */
    public function touch()
    {
        $this->storage->touchKey($this->key);
        $this->timeModified = null;

        return $this;
    }

    /**
     * Checks if file is a directory.
     *
     * @return bool
     */
    public function isDirectory()
    {
        return false;
    }
}