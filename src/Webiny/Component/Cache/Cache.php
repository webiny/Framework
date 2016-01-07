<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Cache;

use Webiny\Component\StdLib\ComponentTrait;

/**
 * Webiny cache component.
 *
 * The cache component is used to store different information into memory or file system for much faster access.
 * The stored information can only be accessed for a limited time, after which it is invalidated.
 *
 *
 * The Memory library is required by this class.
 *
 * @package         Webiny\Component\Cache
 */
class Cache
{
    use ComponentTrait;

    /**
     * Create a cache instance with APC as cache driver.
     *
     * @param array $options Cache options.
     *
     * @return CacheStorage
     */
    public static function APC(array $options = [])
    {
        return new CacheStorage(Storage\APC::getInstance(), $options);
    }

    /**
     * Create a cache instance with Couchbase as cache driver.
     *
     * @param string $user     Couchbase username.
     * @param string $password Couchbase password.
     * @param string $bucket   Couchbase bucket.
     * @param string $host     Couchbase host (with port number).
     * @param array  $options  Cache options.
     *
     * @return CacheStorage
     */
    public static function Couchbase($user, $password, $bucket, $host = '127.0.0.1:8091', $options = [])
    {
        return new CacheStorage(Storage\Couchbase::getInstance($user, $password, $bucket, $host), $options);
    }

    /**
     * Create a cache instance with Memcache as cache driver.
     *
     * @param string $host    Host where the memcached is running.
     * @param int    $port    Port where memcached is running.
     * @param array  $options Cache options.
     *
     * @return CacheStorage
     */
    public static function Memcache($host = '127.0.0.1', $port = 11211, $options = [])
    {
        return new CacheStorage(Storage\Memcache::getInstance($host, $port), $options);
    }

    /**
     * Create a cache instance with Redis as cache driver.
     *
     * @param string $host    Host where the Redis server is running.
     * @param int    $port    Port where Redis server is running.
     * @param array  $options Cache options.
     *
     * @return CacheStorage
     */
    public static function Redis($host = '127.0.0.1', $port = 6379, $options = [])
    {
        return new CacheStorage(Storage\Redis::getInstance($host, $port), $options);
    }

    /**
     * Create a cache instance with BlackHole cache driver.
     * NOTE: BlackHole driver turns off the cache.
     *
     * @return CacheStorage
     */
    public static function BlackHole()
    {
        return new CacheStorage(new Storage\BlackHole());
    }

    /**
     * Create a cache instance with SessionArray cache driver.
     *
     * @return CacheStorage
     */
    public static function SessionArray()
    {
        return new CacheStorage(new Storage\SessionArray());
    }
}