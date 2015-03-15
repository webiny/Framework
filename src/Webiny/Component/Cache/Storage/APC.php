<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Cache\Storage;

use Webiny\Component\Cache\Bridge\CacheStorageInterface;

/**
 * Cache APC storage.
 *
 * @package         Webiny\Component\Cache\Storage
 */
class APC
{

    /**
     * Get an instance of APC cache storage.
     *
     * @return CacheStorageInterface
     */
    public static function getInstance()
    {
        return \Webiny\Component\Cache\Bridge\APC::getInstance();
    }
}