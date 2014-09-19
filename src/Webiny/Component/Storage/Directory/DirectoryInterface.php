<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Storage\Directory;

use Webiny\Component\Storage\Storage;

/**
 * Basic Directory interface
 *
 * @package   Webiny\Component\Storage\Directory
 */
interface DirectoryInterface
{

    /**
     * Constructor
     *
     * @param string      $key           File key
     * @param Storage     $storage       Storage to use
     * @param bool        $treeStructure (Optional) By default, Directory will only read the first level if items.
     *                                   If set to false, Directory will read all children items and list them as one-dimensional array.
     * @param null|string $filter        (Optional) Filter to use when reading directory items
     */
    public function __construct($key, Storage $storage, $treeStructure = true, $filter = null);

    /**
     * Filter items in a directory using given regex or extension.
     *
     * Example 1: '*.pdf' ($condition starting with * means: anything that ends with)
     *
     * Example 2: 'file*' ($condition ending with * means: anything that starts with)
     *
     * Example 3: Any file that ends with `file.zip`: '/(\S+)?file.zip/'
     *
     * @param $condition
     *
     * @return $this DirectoryInterface object containing only filtered values
     */
    public function filter($condition);

    /**
     * Count number of items in a directory
     *
     * @return int Number of items in the directory
     */
    public function count();

    /**
     * Get Storage used by the DirectoryInterface instance
     *
     * @return Storage Storage instance used for this directory
     */
    public function getStorage();

    /**
     * Get directory key
     *
     * @return string Directory key
     */
    public function getKey();

    /**
     * Delete directory and all of it's contents recursively
     *
     * @param bool $fireStorageEvents (Optional) If you don't want to fire StorageEvent::FILE_DELETED set this to false
     *
     * @return bool
     */
    public function delete($fireStorageEvents = true);
}
