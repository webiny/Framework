<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Storage;

use Webiny\Component\EventManager\Event;
use Webiny\Component\Storage\File\File;


/**
 * StorageEvent
 * @package \Webiny\Component\Storage
 */
class StorageEvent extends Event
{

    /**
     * This event is fired after a file was renamed, it also sets a property `oldKey` into the event object.<br />
     * You can use it if you need to know what file was renamed.
     */
    const FILE_RENAMED = "wf.storage.file_renamed";
    /**
     * This event is fired after the file content was written to storage.
     */
    const FILE_SAVED = "wf.storage.file_saved";
    /**
     * This event is fired after the file was deleted from the storage.
     */
    const FILE_DELETED = "wf.storage.file_deleted";

    /**
     * @var File
     */
    protected $_file;

    /**
     * @var Storage
     */
    protected $_storage;

    public function __construct(File $file)
    {
        $this->_file = $file;
        $this->_storage = $file->getStorage();
        parent::__construct();
    }

    /**
     * Get working file
     * @return File
     */
    public function getFile()
    {
        return $this->_file;
    }

    /**
     * @return Storage
     */
    public function getStorage()
    {
        return $this->_storage;
    }
}