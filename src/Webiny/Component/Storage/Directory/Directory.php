<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Storage\Directory;

use Traversable;
use Webiny\Component\EventManager\EventManagerTrait;
use Webiny\Component\Storage\File\File;
use Webiny\Component\Storage\Storage;
use Webiny\Component\Storage\StorageException;
use Webiny\Component\StdLib\StdLibTrait;


/**
 * Directory class used with storage component
 *
 * @package Webiny\Component\Storage\Directory
 */
class Directory implements DirectoryInterface, \IteratorAggregate
{
    use StdLibTrait, EventManagerTrait;

    protected $key;
    protected $storage;
    protected $recursive;
    protected $items;
    protected $regex;

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
        if(!$storage->supportsDirectories()){
            $driver = get_class($storage->getDriver());
            throw new StorageException(StorageException::DRIVER_CAN_NOT_WORK_WITH_DIRECTORIES, [$driver]);
        }

        $this->key = $key;
        $this->recursive = $recursive;
        $this->storage = $storage;

        if ($this->storage->keyExists($key) && !$this->storage->isDirectory($key)) {
            throw new StorageException(StorageException::DIRECTORY_OBJECT_CAN_NOT_READ_FILE_PATHS, [$key]);
        }

        $this->parseFilter($filter);
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
     * @return Directory
     */
    public function filter($condition)
    {
        return new static($this->key, $this->storage, $this->recursive, $condition);
    }

    /**
     * Count number of items in a directory
     *
     * @return int
     */
    public function count()
    {
        $this->loadItems();

        return count($this->items);
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
        $this->loadItems();

        return new \ArrayIterator($this->items);
    }

    /**
     * Get Storage used by the DirectoryInterface instance
     *
     * @return Storage
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * Get directory key
     *
     * @return string Directory key
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Get absolute folder path
     *
     * @return string
     */
    public function getAbsolutePath()
    {
        return $this->storage->getAbsolutePath($this->key);
    }

    protected function parseFilter($filter)
    {
        if (empty($filter)) {
            return;
        }
        $filter = $this->str($filter);
        if ($filter->startsWith('*')) {
            $filter->replace('.', '\.');
            $this->regex = '/(\S+)' . $filter . '/';
        } elseif ($filter->endsWith('*')) {
            $filter->replace('.', '\.');
            $this->regex = '/' . $filter . '(\S+)/';
        } else {
            $this->regex = $filter;
        }
    }

    protected function loadItems()
    {
        if ($this->items === null) {
            $keys = $this->storage->getKeys($this->key, $this->recursive);

            // Filter keys if regex is set
            if ($this->regex) {
                foreach ($keys as $k => $v) {
                    if (!preg_match($this->regex, $v)) {
                        unset($keys[$k]);
                    }
                }
            }
            // Instantiate files/directories
            $this->items = [];
            foreach ($keys as $key) {
                if ($this->storage->isDirectory($key)) {
                    $this->items[$key] = new static($key, $this->storage);
                } else {
                    $this->items[$key] = new File($key, $this->storage);
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
         * If directory was loaded recursively, we do not have the subdirectories in $this->items.
         * We need to reset the items and load directory non-recursively.
         */

        if ($this->recursive) {
            $this->items = null;
            $this->recursive = false;
        }

        $this->loadItems();
        foreach ($this->items as $item) {
            $item->delete();
        }

        if (!$fireStorageEvents) {
            $this->eventManager()->enable();
        }

        return $this->storage->deleteKey($this->key);
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
        $this->loadItems();
        foreach ($this->items as $item) {
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