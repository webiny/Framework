<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Rest\Interfaces;

/**
 * Rest component creates cache key from:
 * - url path
 * - query parameters
 * - http method
 * - post parameters
 * - payload parameters
 * - api version (we use the actual version number, not the aliases like current, and latest)
 *
 * Implement this interface to define your own method for generating a cache key.
 * Some common use cases are to generate a cache key based on some cookie or token.
 * Note that you should still include the url, query parameters and the http method.
 * Always take into account that generating the cache key doesn't actually take longer than getting
 * the data without cache.
 *
 * @package Webiny\Component\Rest\Interfaces
 */

interface CacheKeyInterface
{
    /**
     * Computes and returns a cache key.
     * Best practice is hash it with some hashing algorithm like md5 or sha1.
     * Note that the returned key is used "as it is", nothing is appended to it.
     *
     * @rest.ignore
     *
     * @return string Cache key.
     */
    public function getCacheKey();
}