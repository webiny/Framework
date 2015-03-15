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
    protected $storage;
    protected $key;
    protected $contents;
    protected $isDirectory;
    protected $timeModified;
    protected $url;

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
        $this->storage = $storage;
        $this->key = $key;

        // Make sure a file path is given
        if ($this->storage->keyExists($key) && $this->storage->isDirectory($this->key)) {
            throw new StorageException(StorageException::FILE_OBJECT_CAN_NOT_READ_DIRECTORY_PATHS, [$key]);
        }
    }

    /**
     * @inheritdoc
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * @inheritdoc
     */
    public function getTimeModified($asDateTimeObject = false)
    {
        if ($this->timeModified === null) {
            $this->timeModified = $time = $this->storage->getTimeModified($this->key);
            if ($time) {
                $this->timeModified = $asDateTimeObject ? $this->datetime()->setTimestamp($time) : $time;
            }
        }

        return $this->timeModified;
    }

    /**
     * @inheritdoc
     */
    public function setContents($contents, $append = false)
    {
        $this->contents = $contents;
        if ($this->storage->setContents($this->key, $this->contents, $append) !== false) {
            $this->key = $this->storage->getRecentKey();
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
        if ($this->contents === null) {
            $this->contents = $this->storage->getContents($this->key);
        }

        return $this->contents;
    }

    /**
     * @inheritdoc
     */
    public function rename($newKey)
    {
        if ($this->storage->renameKey($this->key, $newKey)) {
            $event = new StorageEvent($this);
            // Set `oldKey` property that will be available only on rename
            $event->oldKey = $this->key;
            $this->key = $newKey;
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
        if ($this->storage->deleteKey($this->key)) {
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
        return $this->key;
    }

    /**
     * @inheritdoc
     */
    public function getUrl()
    {
        if ($this->url === null) {
            $this->url = $this->storage->getURL($this->key);
        }

        return $this->url;
    }
}
