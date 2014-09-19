<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Cache\Storage;

use Webiny\Component\Cache\Bridge\CacheStorageInterface;

/**
 * Memcache cache storage.
 *
 * @package         Webiny\Component\Cache\Storage
 */
class Memcache
{

    /**
     * Get an instance of Memcache cache storage.
     *
     * @param string $host Host on which memcached is running.
     * @param int    $port Port on which memcached is running.
     *
     * @return CacheStorageInterface
     */
    static function getInstance($host = '127.0.0.1', $port = 11211)
    {
        return \Webiny\Component\Cache\Bridge\Memcache::getInstance($host, $port);
    }
}