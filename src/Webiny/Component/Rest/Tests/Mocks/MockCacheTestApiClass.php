<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Rest\Tests\Mocks;

use Webiny\Component\Rest\Interfaces\CacheKeyInterface;

class MockCacheTestApiClass implements CacheKeyInterface
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
    public function getCacheKey()
    {
        return md5('key');
    }
}