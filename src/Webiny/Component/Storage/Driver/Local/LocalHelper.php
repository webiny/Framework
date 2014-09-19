<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Storage\Driver\Local;

use Webiny\Component\Storage\StorageException;
use Webiny\Component\StdLib\SingletonTrait;

/**
 * Local storage driver helper
 *
 * @package   Webiny\Component\Storage\Driver\Local
 *
 * The class was taken from KnpLabs-Gaufrette library and was adapted to suite Webiny Framework
 * Original author: Antoine Hérault <antoine.herault@gmail.com>
 */
class LocalHelper
{
    use SingletonTrait;

    /**
     * Build absolute path by given $key and $directory
     *
     * @param $key
     * @param $directory
     * @param $create
     *
     * @return mixed
     */
    public function buildPath($key, $directory, $create)
    {
        $this->ensureDirectoryExists($directory, $create);

        return $this->normalizeDirectoryPath($directory . '/' . $key);
    }

    /**
     * Gets the key using file $path and storage $directory
     *
     * @param string $path      Path to extract file key from
     *
     * @param string $directory Directory of the storage
     *
     * @return string
     */
    public function getKey($path, $directory)
    {
        $path = $this->_normalizePath($path);

        return ltrim(substr($path, strlen($directory)), '/');
    }

    /**
     * Make sure the target directory exists
     *
     * @param string $directory Path to check
     * @param bool   $create    (Optional) Create path if doesn't exist
     *
     * @throws \Webiny\Component\Storage\StorageException
     */
    public function ensureDirectoryExists($directory, $create = false)
    {
        if (!is_dir($directory)) {
            if (!$create) {
                throw new StorageException(StorageException::DIRECTORY_DOES_NOT_EXIST, [$directory]);
            }
            $this->_createDirectory($directory);
        }
    }

    /**
     * Normalize path (strip '.', '..' and make sure it's not a symlink)
     *
     * @param $path
     *
     * @return string
     */
    public function normalizeDirectoryPath($path)
    {
        $path = $this->_normalizePath($path);

        if (is_link($path)) {
            $path = realpath($path);
        }

        return $path;
    }

    /**
     * Create directory
     *
     * @param string $directory Directory path to create
     *
     * @throws \Webiny\Component\Storage\StorageException
     */
    protected function _createDirectory($directory)
    {
        $umask = umask(0);
        $created = mkdir($directory, 0777, true);
        umask($umask);

        if (!$created) {
            throw new StorageException(StorageException::DIRECTORY_COULD_NOT_BE_CREATED, [$directory]);
        }
    }

    /**
     * Normalizes the given path
     *
     * @param string $path
     *
     * @return string
     */
    protected function _normalizePath($path)
    {
        $path = str_replace('\\', '/', $path);
        $prefix = $this->_getAbsolutePrefix($path);
        $path = substr($path, strlen($prefix));
        $parts = array_filter(explode('/', $path), 'strlen');
        $tokens = array();

        foreach ($parts as $part) {
            switch ($part) {
                case '.':
                    continue;
                    break;
                case '..':
                    if (count($tokens) !== 0) {
                        array_pop($tokens);
                        continue;
                    } elseif (!empty($prefix)) {
                        continue;
                    }
                    break;
                default:
                    $tokens[] = $part;
            }
        }

        return $prefix . implode('/', $tokens);
    }

    /**
     * Returns the absolute prefix of the given path
     *
     * @param string $path A normalized path
     *
     * @return string
     */
    protected function _getAbsolutePrefix($path)
    {
        preg_match('|^(?P<prefix>([a-zA-Z]:)?/)|', $path, $matches);

        if (empty($matches['prefix'])) {
            return '';
        }

        return strtolower($matches['prefix']);
    }
}