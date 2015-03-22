<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Storage\File;

use Webiny\Component\Storage\Storage;

/**
 * Basic File interface
 *
 * @package   Webiny\Component\Storage\File
 */
interface FileInterface
{
    /**
     * Constructor
     *
     * @param string  $key     File key
     * @param Storage $storage Storage to use
     */
    public function __construct($key, Storage $storage);

    /**
     * Get file storage
     *
     * @return Storage
     */
    public function getStorage();

    /**
     * Get file key
     *
     * @return string
     */
    public function getKey();

    /**
     * Get file public URL
     *
     * @return string
     */
    public function getUrl();

    /**
     * Get file contents
     *
     * @throws StorageException
     *
     * @return string|boolean String on success, false if could not read content
     */
    public function getContents();

    /**
     * Get time modified
     *
     * @param bool $asDateTimeObject
     *
     * @return int|DateTimeObject UNIX timestamp or DateTimeObject
     */
    public function getTimeModified($asDateTimeObject = false);

    /**
     * Set file contents (writes contents to storage)<br />
     *
     * Fires an event StorageEvent::FILE_SAVED after the file content was written.
     *
     * @param mixed $contents
     *
     * @param bool  $append
     *
     * @return $this
     */
    public function setContents($contents, $append = false);

    /**
     * Rename a file<br />
     *
     * Fires an event StorageEvent::FILE_RENAMED after the file was renamed.
     *
     * @param string $newKey New file name
     *
     * @return bool
     */
    public function rename($newKey);

    /**
     * Delete a file<br />
     *
     * Fires an event StorageEvent::FILE_DELETED after the file was deleted.
     *
     * @return bool
     */
    public function delete();

    /**
     * Get absolute file path.
     * If storage driver does not support absolute paths (cloud storage) returns file key
     *
     * @return string
     */
    public function getAbsolutePath();

    /**
     * Get file size in bytes
     *
     * @return int|null Number of bytes or null
     */
    public function getSize();

    /**
     * Touch a file (change time modified)
     *
     * @return $this
     */
    public function touch();
}
