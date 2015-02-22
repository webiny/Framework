<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Storage\File;

use Webiny\Component\EventManager\EventManagerTrait;
use Webiny\Component\Storage\Storage;
use Webiny\Component\Storage\StorageEvent;
use Webiny\Component\Storage\StorageException;
use Webiny\Component\StdLib\StdObjectTrait;

/**
 * Basic File object that supports all common storage methods
 *
 * @package  Webiny\Component\Storage\File
 */
class File implements FileInterface
{
    use StdObjectTrait, EventManagerTrait;

    /**
     * @var Storage
     */
    protected $_storage;
    protected $_key;
    protected $_contents;
    protected $_isDirectory;
    protected $_timeModified;
    protected $_url;

    /**
     * Construct a File instance
     *
     * @param string  $key     File key
     * @param Storage $storage Storage to use
     *
     * @throws \Webiny\Component\Storage\StorageException
     * @return \Webiny\Component\Storage\File\File
     */
    public function __construct($key, Storage $storage)
    {
        $this->_storage = $storage;
        $this->_key = $key;

        // Make sure a file path is given
        if ($this->_storage->keyExists($key) && $this->_storage->isDirectory($this->_key)) {
            throw new StorageException(StorageException::FILE_OBJECT_CAN_NOT_READ_DIRECTORY_PATHS, [$key]);
        }
    }

    /**
     * @inheritdoc
     */
    public function getStorage()
    {
        return $this->_storage;
    }

    /**
     * @inheritdoc
     */
    public function getTimeModified($asDateTimeObject = false)
    {
        if ($this->_timeModified === null) {
            $this->_timeModified = $time = $this->_storage->getTimeModified($this->_key);
            if ($time) {
                $this->_timeModified = $asDateTimeObject ? $this->datetime()->setTimestamp($time) : $time;
            }
        }

        return $this->_timeModified;
    }

    /**
     * @inheritdoc
     */
    public function setContents($contents, $append = false)
    {
        $this->_contents = $contents;
        if ($this->_storage->setContents($this->_key, $this->_contents, $append) !== false) {
            $this->_key = $this->_storage->getRecentKey();
            $this->eventManager()->fire(StorageEvent::FILE_SAVED, new StorageEvent($this));

            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function getContents()
    {
        if ($this->_contents === null) {
            $this->_contents = $this->_storage->getContents($this->_key);
        }

        return $this->_contents;
    }

    /**
     * @inheritdoc
     */
    public function rename($newKey)
    {
        if ($this->_storage->renameKey($this->_key, $newKey)) {
            $event = new StorageEvent($this);
            // Set `oldKey` property that will be available only on rename
            $event->oldKey = $this->_key;
            $this->_key = $newKey;
            $this->eventManager()->fire(StorageEvent::FILE_RENAMED, $event);

            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function delete()
    {
        if ($this->_storage->deleteKey($this->_key)) {
            $this->eventManager()->fire(StorageEvent::FILE_DELETED, new StorageEvent($this));

            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function getKey()
    {
        return $this->_key;
    }

    /**
     * @inheritdoc
     */
    public function getUrl()
    {
        if ($this->_url === null) {
            $this->_url = $this->_storage->getURL($this->_key);
        }

        return $this->_url;
    }
}
