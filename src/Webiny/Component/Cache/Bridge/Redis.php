<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Cache\Bridge;

use Webiny\Component\Cache\Cache;

/**
 * Redis cache bridge loader.
 *
 * @package         Webiny\Component\Cache\Bridge
 */
class Redis extends AbstractCache
{

    /**
     * Path to the default bridge library for APC.
     *
     * @var string
     */
    private static $library = '\Webiny\Component\Cache\Bridge\Memory\Redis';

    /**
     * Get the name of bridge library which will be used as the driver.
     *
     * @return string
     */
    public static function getLibrary()
    {
        return Cache::getConfig()->get('Bridges.Redis', self::$library);
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
     * @param string $host Host on which Redis server is running.
     * @param int    $port Port on which Redis server is running.
     *
     * @throws CacheException
     * @return void|CacheStorageInterface
     */
    public static function getInstance($host = '127.0.0.1', $port = 6379)
    {
        $driver = static::getLibrary();

        try {
            $instance = new $driver($host, $port);
        } catch (\Exception $e) {
            throw new CacheException($e->getMessage());
        }

        if (!self::isInstanceOf($instance, '\Webiny\Component\Cache\Bridge\CacheStorageInterface')) {
            throw new CacheException(CacheException::MSG_INVALID_ARG, [
                    'driver',
                    '\Webiny\Component\Cache\Bridge\CacheStorageInterface'
                ]
            );
        }

        return $instance;
    }
}