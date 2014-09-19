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
class Memcache extends CacheAbstract
{

    /**
     * Path to the default bridge library for APC.
     *
     * @var string
     */
    private static $_library = '\Webiny\Component\Cache\Bridge\Memory\Memcache';

    /**
     * Get the name of bridge library which will be used as the driver.
     *
     * @return string
     */
    static function _getLibrary()
    {
        return Cache::getConfig()->get('Bridges.Memcache', self::$_library);
    }

    /**
     * Change the default library used for the driver.
     *
     * @param string $pathToClass Path to the new driver class. Must be an instance of \Webiny\Component\Cache\Bridge\CacheInterface
     */
    static function setLibrary($pathToClass)
    {
        self::$_library = $pathToClass;
    }

    /**
     * Override the CacheAbstract::getInstance method.
     *
     * @see CacheAbstract::getInstance()
     *
     * @param string $host Host on which memcached is running.
     * @param int    $port Port on which memcached is running.
     *
     * @throws CacheException
     * @return void|CacheStorageInterface
     */
    static function getInstance($host = '127.0.0.1', $port = 11211)
    {
        $driver = static::_getLibrary();

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