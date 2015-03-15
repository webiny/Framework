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

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

/**
 * Local storage
 *
 * @package   Webiny\Component\Storage\Driver\Local
 */
class Local implements DirectoryAwareInterface, DriverInterface, SizeAwareInterface, AbsolutePathInterface, TouchableInterface
{

    protected $dateFolderStructure;
    protected $recentKey = null;
    protected $directory;
    protected $create;

    /**
     * Constructor
     *
     * @param string  $directory           Directory of the storage
     * @param string  $publicUrl           Public storage URL
     * @param bool    $dateFolderStructure If true, will append Y/m/d to the key
     * @param boolean $create              Whether to create the directory if it does not
     *                                     exist (default FALSE)
     *
     * @throws StorageException
     */
    public function __construct($directory, $publicUrl = '', $dateFolderStructure = false, $create = false)
    {
        $this->helper = LocalHelper::getInstance();
        $this->directory = $this->helper->normalizeDirectoryPath($directory);
        $this->publicUrl = $publicUrl;
        $this->dateFolderStructure = $dateFolderStructure;
        $this->create = $create;
    }

    /**
     * @inheritdoc
     */
    public function getTimeModified($key)
    {
        $this->recentKey = $key;

        if ($this->keyExists($key)) {
            return filemtime($this->buildPath($key));
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function getSize($key)
    {
        $this->recentKey = $key;
        if ($this->keyExists($key)) {
            return filesize($this->buildPath($key));
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function touchKey($key)
    {
        $this->recentKey = $key;

        return touch($this->buildPath($key));
    }

    /**
     * @inheritdoc
     */
    public function renameKey($sourceKey, $targetKey)
    {
        $this->recentKey = $sourceKey;
        if ($this->keyExists($sourceKey)) {
            $targetPath = $this->buildPath($targetKey);
            $this->helper->ensureDirectoryExists(dirname($targetPath), true);

            return rename($this->buildPath($sourceKey), $targetPath);
        }
        throw new StorageException(StorageException::FILE_NOT_FOUND);
    }

    /**
     * @inheritdoc
     */
    public function getContents($key)
    {
        $this->recentKey = $key;
        $data = file_get_contents($this->buildPath($key));
        if ($data === false) {
            throw new StorageException(StorageException::FAILED_TO_READ);
        }

        return $data;
    }

    /**
     * @inheritdoc
     */
    public function setContents($key, $contents, $append = false)
    {
        if ($this->dateFolderStructure) {
            if (!$this->keyExists($key)) {
                $key = new StringObject($key);
                $key = date('Y' . DS . 'm' . DS . 'd') . DS . $key->trimLeft(DS);
            }
        }
        $this->recentKey = $key;

        $path = $this->buildPath($key);
        $this->helper->ensureDirectoryExists(dirname($path), true);

        return file_put_contents($path, $contents, $append ? FILE_APPEND : null);
    }

    /**
     * @inheritdoc
     */
    public function keyExists($key)
    {
        $this->recentKey = $key;

        return file_exists($this->buildPath($key));
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
        if ($key != '') {
            $key = ltrim($key, DS);
            $key = rtrim($key, DS);
            $path = $this->directory . DS . $key;
        } else {
            $path = $this->directory;
        }

        $this->helper->ensureDirectoryExists($path, $this->create);

        if ($recursive) {
            try {
                $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path,
                                                                                           \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::UNIX_PATHS
                                                           )
                );
                if ($recursive > -1) {
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
                if ($name == '.' || $name == '..') {
                    continue;
                }
                $files[] = $fileinfo->getPathname();
            }
        }

        $keys = array();


        foreach ($files as $file) {
            $keys[] = $this->helper->getKey($file, $this->directory);
        }
        sort($keys);


        return $keys;
    }

    /**
     * @inheritdoc
     */
    public function deleteKey($key)
    {
        $this->recentKey = $key;
        $path = $this->buildPath($key);

        if ($this->isDirectory($key)) {
            return @rmdir($path);
        }

        return @unlink($path);
    }

    /**
     * @inheritdoc
     */
    public function getAbsolutePath($key)
    {
        $this->recentKey = $key;

        return $this->buildPath($key);
    }

    /**
     * @inheritdoc
     */
    public function getURL($key)
    {
        $key = str_replace('\\', '/', $key);

        return $this->publicUrl . '/' . ltrim($key, "/");
    }


    /**
     * @inheritdoc
     */
    public function getRecentKey()
    {
        return $this->recentKey;
    }

    /**
     * @inheritdoc
     */
    public function isDirectory($key)
    {
        return is_dir($this->buildPath($key));
    }

    private function buildPath($key)
    {
        $path = $this->helper->buildPath($key, $this->directory, $this->create);
        if (strpos($path, $this->directory) !== 0) {
            throw new StorageException(StorageException::PATH_IS_OUT_OF_STORAGE_ROOT, [
                                                                                        $path,
                                                                                        $this->directory
                                                                                    ]
            );
        }

        return $path;
    }
}