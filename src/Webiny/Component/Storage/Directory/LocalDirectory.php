<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Storage\Directory;

use Traversable;
use Webiny\Component\EventManager\EventManagerTrait;
use Webiny\Component\Storage\Driver\Local\LocalHelper;
use Webiny\Component\Storage\File\LocalFile;
use Webiny\Component\Storage\Storage;
use Webiny\Component\Storage\StorageException;
use Webiny\Component\StdLib\StdLibTrait;


/**
 * Directory class used with storage component
 *
 * @package Webiny\Component\Storage\Directory
 */
class LocalDirectory implements DirectoryInterface, \IteratorAggregate
{
    use StdLibTrait, EventManagerTrait;

    protected $_key;
    protected $_storage;
    protected $_recursive;
    protected $_items;
    protected $_regex;

    /**
     * Constructor
     *
     * @param string      $key               File key
     * @param Storage     $storage           Storage to use
     * @param bool        $recursive         (Optional) By default, Directory will only read the first level if items.
     *                                       If set to true, Directory will read all children items and list them as one-dimensional array.
     * @param null|string $filter            (Optional) Filter to use when reading directory items
     *
     * @throws \Webiny\Component\Storage\StorageException
     */
    public function __construct($key, Storage $storage, $recursive = false, $filter = null)
    {
        $this->_key = $key;
        $this->_recursive = $recursive;
        $this->_storage = $storage;

        if ($this->_storage->keyExists($key) && !$this->_storage->isDirectory($key)) {
            throw new StorageException(StorageException::DIRECTORY_OBJECT_CAN_NOT_READ_FILE_PATHS, [$key]);
        }

        $this->_parseFilter($filter);
    }

    /**
     * Filter items in a directory using given regex or extension.
     *
     * Example 1: '*.pdf' ($condition starting with * means: anything that ends with)
     *
     * Example 2: 'file*' ($condition ending with * means: anything that starts with)
     *
     * Example 3: Any file that ends with `file.zip`: '/(\S+)?file.zip/'
     *
     * @param string $condition
     *
     * @return LocalDirectory
     */
    public function filter($condition)
    {
        return new static($this->_key, $this->_storage, $this->_recursive, $condition);
    }

    /**
     * Count number of items in a directory
     *
     * @return int
     */
    public function count()
    {
        $this->_loadItems();

        return count($this->_items);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     */
    public function getIterator()
    {
        $this->_loadItems();

        return new \ArrayIterator($this->_items);
    }

    /**
     * Get Storage used by the DirectoryInterface instance
     *
     * @return Storage
     */
    public function getStorage()
    {
        return $this->_storage;
    }

    /**
     * Get directory key
     *
     * @return string Directory key
     */
    public function getKey()
    {
        return $this->_key;
    }

    protected function _parseFilter($filter)
    {
        if (empty($filter)) {
            return;
        }
        $filter = $this->str($filter);
        if ($filter->startsWith('*')) {
            $filter->replace('.', '\.');
            $this->_regex = '/(\S+)' . $filter . '/';
        } elseif ($filter->endsWith('*')) {
            $filter->replace('.', '\.');
            $this->_regex = '/' . $filter . '(\S+)/';
        } else {
            $this->_regex = $filter;
        }
    }

    protected function _loadItems()
    {
        if ($this->_items == null) {
            $keys = $this->_storage->getKeys($this->_key, $this->_recursive);

            // Filter keys if regex is set
            if ($this->_regex) {
                foreach ($keys as $k => $v) {
                    if (!preg_match($this->_regex, $v)) {
                        unset($keys[$k]);
                    }
                }
            }
            // Instantiate files/directories
            $this->_items = [];
            foreach ($keys as $key) {
                if ($this->_storage->isDirectory($key)) {
                    $this->_items[$key] = new static($key, $this->_storage);
                } else {
                    $this->_items[$key] = new LocalFile($key, $this->_storage);
                }
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function delete($fireStorageEvents = true)
    {
        if (!$fireStorageEvents) {
            $this->eventManager()->disable();
        }
        /**
         * If directory was loaded recursively, we do not have the subdirectories in $this->_items.
         * We need to reset the items and load directory non-recursively.
         */

        if ($this->_recursive) {
            $this->_items = null;
            $this->_recursive = false;
        }

        $this->_loadItems();
        foreach ($this->_items as $item) {
            $item->delete();
        }

        if (!$fireStorageEvents) {
            $this->eventManager()->enable();
        }

        return $this->_storage->deleteKey($this->_key);
    }

    /**
     * Get directory size
     *
     * WARNING! This is a very intensive operation especially on deep directory structures!
     * It is performed by recursively walking through directory structure and getting each file's size.
     */
    public function getSize()
    {
        $size = 0;
        $this->_loadItems();
        foreach ($this->_items as $item) {
            $size += $item->getSize();
        }

        return $size;
    }

    /**
     * Checks if file is a directory.
     *
     * @return bool
     */
    public static function isDirectory()
    {
        return true;
    }
}