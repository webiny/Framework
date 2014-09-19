<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Cache\Storage;

use Webiny\Component\Cache\Bridge\CacheStorageInterface;

/**
 * Couchbase cache storage.
 *
 * @package         Webiny\Component\Cache\Storage
 */
class Couchbase
{
    /**
     * Get an instance of Couchbase cache storage.
     *
     * @param string $user     Couchbase username.
     * @param string $password Couchbase password.
     * @param string $bucket   Couchbase bucket.
     * @param string $host     Couchbase host (with port number).
     *
     * @return CacheStorageInterface
     */
    static function getInstance($user, $password, $bucket, $host)
    {
        return \Webiny\Component\Cache\Bridge\Couchbase::getInstance($user, $password, $bucket, $host);
    }
}