<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Cache\Bridge;

use Webiny\Component\Cache\Cache;

/**
 * Memcache cache bridge loader.
 *
 * @package         Webiny\Component\Cache\Bridge
 */
class Memcache extends AbstractCache
{

    /**
     * Path to the default bridge library for APC.
     *
     * @var string
     */
    private static $library = Memory\Memcache::class;

    /**
     * Get the name of bridge library which will be used as the driver.
     *
     * @return string
     */
    public static function getLibrary()
    {
        return Cache::getConfig()->get('Bridges.Memcache', self::$library);
    }

    /**
     * Change the default library used for the driver.
     *
     * @param string $pathToClass Path to the new driver class. Must be an instance of \Webiny\Component\Cache\Bridge\CacheInterface
     */
    public static function setLibrary($pathToClass)
    {
        self::$library = $pathToClass;
    }

    /**
     * Override the AbstractCache::getInstance method.
     *
     * @see CacheAbstract::getInstance()
     *
     * @param string $host Host on which memcached is running.
     * @param int    $port Port on which memcached is running.
     *
     * @throws CacheException
     * @return void|CacheStorageInterface
     */
    public static function getInstance($host = '127.0.0.1', $port = 11211)
    {
        $driver = static::getLibrary();

        try {
            $instance = new $driver($host, $port);
        } catch (\Exception $e) {
            throw new CacheException($e->getMessage());
        }

        if (!self::isInstanceOf($instance, CacheStorageInterface::class)) {
            throw new CacheException(CacheException::MSG_INVALID_ARG, ['driver', CacheStorageInterface::class]);
        }

        return $instance;
    }
}