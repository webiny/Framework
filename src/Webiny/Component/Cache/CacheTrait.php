<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Cache;

use Webiny\Component\ServiceManager\ServiceManager;
use Webiny\Component\ServiceManager\ServiceManagerException;

/**
 * Cache trait.
 *
 * @package         Webiny\Component\Cache
 */
trait CacheTrait
{
    /**
     * Returns instance of cache driver.
     * If instance with the given $cacheId doesn't exist, ServiceManagerException is thrown.
     *
     * @param string $cacheId Name of the cache driver
     *
     * @throws \Webiny\Component\ServiceManager\ServiceManagerException
     * @return CacheStorage
     */
    protected static function cache($cacheId)
    {
        try {
            return ServiceManager::getInstance()->getService('Cache.' . $cacheId);
        } catch (ServiceManagerException $e) {
            throw $e;
        }
    }
}