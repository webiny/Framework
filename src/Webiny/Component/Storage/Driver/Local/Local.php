<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Storage\Driver\Local;

use Webiny\Component\Storage\Driver\DriverInterface;
use Webiny\Component\Storage\Driver\AbsolutePathInterface;
use Webiny\Component\Storage\Driver\DirectoryAwareInterface;
use Webiny\Component\Storage\Driver\SizeAwareInterface;
use Webiny\Component\Storage\Driver\TouchableInterface;
use Webiny\Component\Storage\StorageException;
use Webiny\Component\StdLib\StdObject\StringObject\StringObject;

/**
 * Local storage
 *
 * @package   Webiny\Component\Storage\Driver\Local
 */
class Local implements DirectoryAwareInterface, DriverInterface, SizeAwareInterface, AbsolutePathInterface, TouchableInterface
{

    protected $_dateFolderStructure;
    protected $_recentKey = null;
    protected $_directory;
    protected $_create;

    /**
     * Constructor
     *
     * @param string  $directory           Directory of the storage
     * @param string $publicUrl            Public storage URL
     * @param bool    $dateFolderStructure If true, will append Y/m/d to the key
     * @param boolean $create              Whether to create the directory if it does not
     *                                     exist (default FALSE)
     *
     * @throws StorageException
     */
    public function __construct($directory, $publicUrl = '', $dateFolderStructure = false, $create = false)
    {
        $this->_helper = LocalHelper::getInstance();
        $this->_directory = $this->_helper->normalizeDirectoryPath($directory);
        $this->_publicUrl = $publicUrl;
        $this->_dateFolderStructure = $dateFolderStructure;
        $this->_create = $create;
    }


    /**
     * @inheritdoc
     */
    public function getTimeModified($key)
    {
        $this->_recentKey = $key;

        if($this->keyExists($key)) {
            return filemtime($this->_buildPath($key));
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function getSize($key)
    {
        $this->_recentKey = $key;
        if($this->keyExists($key)) {
            return filesize($this->_buildPath($key));
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function touchKey($key)
    {
        $this->_recentKey = $key;

        return touch($this->_buildPath($key));
    }

    /**
     * @inheritdoc
     */
    public function renameKey($sourceKey, $targetKey)
    {
        $this->_recentKey = $sourceKey;
        if($this->keyExists($sourceKey)) {
            $targetPath = $this->_buildPath($targetKey);
            $this->_helper->ensureDirectoryExists(dirname($targetPath), true);

            return rename($this->_buildPath($sourceKey), $targetPath);
        }
        throw new StorageException(StorageException::FILE_NOT_FOUND);
    }

    /**
     * @inheritdoc
     */
    public function getContents($key)
    {
        $this->_recentKey = $key;
        $data = file_get_contents($this->_buildPath($key));
        if(!$data) {
            throw new StorageException(StorageException::FAILED_TO_READ);
        }

        return $data;
    }

    /**
     * @inheritdoc
     */
    public function setContents($key, $contents)
    {
        if($this->_dateFolderStructure) {
            if(!$this->keyExists($key)) {
                $key = new StringObject($key);
                $key = date('Y' . DIRECTORY_SEPARATOR . 'm' . DIRECTORY_SEPARATOR . 'd'
                    ) . DIRECTORY_SEPARATOR . $key->trimLeft(DIRECTORY_SEPARATOR);
            }
        }
        $this->_recentKey = $key;

        $path = $this->_buildPath($key);
        $this->_helper->ensureDirectoryExists(dirname($path), true);

        return file_put_contents($path, $contents);
    }

    /**
     * @inheritdoc
     */
    public function keyExists($key)
    {
        $this->_recentKey = $key;

        return file_exists($this->_buildPath($key));
    }


    /**
     * Returns an array of all keys (files and directories)
     *
     * @param string   $key       (Optional) Key of a directory to get keys from. If not set - keys will be read from the storage root.
     *
     * @param bool|int $recursive (Optional) Read all items recursively. Pass integer value to specify recursion depth.
     *
     * @return array
     */
    public function getKeys($key = '', $recursive = false)
    {
        if($key != '') {
            $key = ltrim($key, DIRECTORY_SEPARATOR);
            $key = rtrim($key, DIRECTORY_SEPARATOR);
            $path = $this->_directory . DIRECTORY_SEPARATOR . $key;
        } else {
            $path = $this->_directory;
        }

        $this->_helper->ensureDirectoryExists($path, $this->_create);

        if($recursive) {
            try {
                $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path,
                                                                                           \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::UNIX_PATHS
                                                           )
                );
                if($recursive > -1) {
                    $iterator->setMaxDepth($recursive);
                }
            } catch (\Exception $e) {
                $iterator = new \EmptyIterator;
            }
            $files = iterator_to_array($iterator);
        } else {
            $files = [];
            $iterator = new \DirectoryIterator($path);
            foreach ($iterator as $fileinfo) {
                $name = $fileinfo->getFilename();
                if($name == '.' || $name == '..') {
                    continue;
                }
                $files[] = $fileinfo->getPathname();
            }
        }

        $keys = array();


        foreach ($files as $file) {
            $keys[] = $this->_helper->getKey($file, $this->_directory);
        }
        sort($keys);


        return $keys;
    }

    /**
     * @inheritdoc
     */
    public function deleteKey($key)
    {
        $this->_recentKey = $key;
        $path = $this->_buildPath($key);

        if($this->isDirectory($key)) {
            return @rmdir($path);
        }

        return @unlink($path);
    }

    /**
     * @inheritdoc
     */
    public function getAbsolutePath($key)
    {
        $this->_recentKey = $key;

        return $this->_buildPath($key);
    }

    /**
     * @inheritdoc
     */
    public function getURL($key)
    {
        $key = str_replace('\\', '/', $key);

        return $this->_publicUrl . '/' . ltrim($key, "/");
    }


    /**
     * @inheritdoc
     */
    public function getRecentKey()
    {
        return $this->_recentKey;
    }

    /**
     * @inheritdoc
     */
    public function isDirectory($key)
    {
        return is_dir($this->_buildPath($key));
    }

    private function _buildPath($key)
    {
        $path = $this->_helper->buildPath($key, $this->_directory, $this->_create);
        if(strpos($path, $this->_directory) !== 0) {
            throw new StorageException(StorageException::PATH_IS_OUT_OF_STORAGE_ROOT, [
                    $path,
                    $this->_directory
                ]
            );
        }

        return $path;
    }
}