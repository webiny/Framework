<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Cache\Storage;

use Webiny\Component\Cache\Bridge\CacheStorageInterface;

/**
 * Redis cache storage.
 *
 * @package         Webiny\Component\Cache\Storage
 */
class Redis
{

    /**
     * Get an instance of Redis cache storage.
     *
     * @param string $host Host on which Redis server is running.
     * @param int    $port Port on which Redis server is running.
     *
     * @return CacheStorageInterface
     */
    static function getInstance($host = 'localhost', $port = 6379)
    {
        return \Webiny\Component\Cache\Bridge\Redis::getInstance($host, $port);
    }
}