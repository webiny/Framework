<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Cache\Storage;

use \Webiny\Component\Cache\Bridge\CacheStorageInterface;

/**
 * BlackHole cache storage.
 * This storage is used when cache status is set to false.
 *
 * @package         Webiny\Component\Cache\Storage
 */
class BlackHole implements CacheStorageInterface
{

    /**
     * Save a value into memory only if it DOESN'T exists (or false will be returned).
     *
     * @param string       $key   Name of the key.
     * @param mixed        $value Value you wish to save.
     * @param int          $ttl   For how long to store value. (in seconds)
     * @param array|string $tags  Tags you wish to assign to this cache entry.
     *
     * @return boolean True if value was added, otherwise false.
     */
    public function add($key, $value, $ttl = 600, $tags = null)
    {
        return false;
    }

    /**
     * Save a value into memory.
     *
     * @param string       $key   Name of the key.
     * @param mixed        $value Value you wish to save.
     * @param int          $ttl   For how long to store value. (in seconds)
     * @param array|string $tags  Tags you wish to assign to this cache entry.
     *
     * @return bool True if value was stored successfully, otherwise false.
     */
    public function save($key, $value, $ttl = 600, $tags = null)
    {
        return false;
    }

    /**
     * Get the cache data for the given $key.
     *
     * @param string|array $key     Name of the cache key.
     * @param mixed        $ttlLeft = (ttl - time()) of key. Use to exclude dog-pile effect, with lock/unlock_key methods.
     *
     * @return mixed
     */
    public function read($key, &$ttlLeft = -1)
    {
        return false;
    }

    /**
     * Delete key or array of keys from storage.
     *
     * @param string|array $key Key, or array of keys, you wish to delete.
     *
     * @return boolean|array If array of keys was passed, on error will be returned array of not deleted keys, or true on success.
     */
    public function delete($key)
    {
        return true;
    }

    /**
     * Delete expired cache values.
     *
     * @return boolean
     */
    public function deleteOld()
    {
        return true;
    }

    /**
     * Delete keys by tags.
     *
     * @param array|string $tag Tag, or an array of tags, for which you wish to delete the cache.
     *
     * @return boolean
     */
    public function deleteByTags($tag)
    {
        return true;
    }

    /**
     * Select from storage via callback function.
     * Only values of array type will be selected.
     *
     * @param callable $callback ($value_array,$key)
     * @param bool     $getArray
     *
     * @return mixed
     */
    public function selectByCallback($callback, $getArray = false)
    {
        return false;
    }

    /**
     * Increment value of the key.
     *
     * @param string $key               Name of the cache key.
     * @param mixed  $byValue
     *                                  If stored value is an array:
     *                                  - If $by_value is a value in array, new element will be pushed to the end of array,
     *                                  - If $by_value is a key=>value array, new key=>value pair will be added (or updated).
     * @param int    $limitKeysCount    Maximum count of elements (used only if stored value is array).
     * @param int    $ttl               Set time to live for key.
     *
     * @return int|string|array New key value.
     */
    public function increment($key, $byValue = 1, $limitKeysCount = 0, $ttl = 259200)
    {
        return true;
    }

    /**
     * Get exclusive mutex for key. Key will be still accessible to read and write, but
     * another process can exclude dog-pile effect, if before updating the key he will try to get this mutex.
     *
     * @param mixed $key                  Name of the cache key.
     * @param mixed $autoUnlockerVariable Pass empty, just declared variable
     *
     * @return bool
     */
    public function lockKey($key, &$autoUnlockerVariable)
    {
        return true;
    }

    /**
     * Try to lock key, and if key is already locked - wait, until key will be unlocked.
     * Time of waiting is defined in max_wait_unlock constant of MemoryObject class.
     *
     * @param string $key Name of the cache key.
     * @param        $autoUnlocker
     *
     * @return boolean
     */
    public function acquireKey($key, &$autoUnlocker)
    {
        return false;
    }
}