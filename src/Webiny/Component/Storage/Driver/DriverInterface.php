<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Storage\Driver;

/**
 * DriverInterface
 *
 * @package   Webiny\Component\Storage\Driver
 */

interface DriverInterface
{
    /**
     * Reads the contents of the file
     *
     * @param string $key
     *
     * @return string|boolean if cannot read content
     */
    public function getContents($key);

    /**
     * Writes the given File
     *
     * @param      $key
     * @param      $contents
     *
     * @param bool $append
     *
     * @return bool|int The number of bytes that were written into the file
     */
    public function setContents($key, $contents, $append = false);

    /**
     * Checks whether the file exists
     *
     * @param string $key
     *
     * @return boolean
     */
    public function keyExists($key);

    /**
     * Returns an array of all keys (files and directories)
     *
     * For storages that do not support directories, both parameters are irrelevant.
     *
     * @param string   $key       (Optional) Key of a directory to get keys from. If not set - keys will be read from the storage root.
     *
     * @param bool|int $recursive (Optional) Read all items recursively. Pass integer value to specify recursion depth.
     *
     * @return array
     */
    public function getKeys($key = '', $recursive = false);

    /**
     * Returns the last modified time
     *
     * @param string $key
     *
     * @return integer|boolean A UNIX like timestamp or false
     */
    public function getTimeModified($key);

    /**
     * Deletes the file
     *
     * @param string $key
     *
     * @return boolean
     */
    public function deleteKey($key);

    /**
     * Renames a file
     *
     * @param string $sourceKey
     * @param string $targetKey
     *
     * @return boolean
     */
    public function renameKey($sourceKey, $targetKey);

    /**
     * Returns most recent file key that was used by a storage
     *
     * @return string|null
     */
    public function getRecentKey();

    /**
     * Returns public file URL
     *
     * @param $key
     *
     * @return mixed
     */
    public function getURL($key);

    /**
     * Does this storage create a date folder structure?
     * @return boolean
     */
    public function createDateFolderStructure();
}