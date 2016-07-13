<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Cache\Bridge;

use Webiny\Component\Cache\Cache;

/**
 * APC cache bridge loader.
 *
 * @package         Webiny\Component\Cache\Bridge
 */
class APC extends AbstractCache
{

    /**
     * Path to the default bridge library for APC.
     *
     * @var string
     */
    private static $library = '\Webiny\Component\Cache\Bridge\Memory\APC';

    /**
     * Get the name of bridge library which will be used as the driver.
     *
     * @return string
     */
    public static function getLibrary()
    {
        return Cache::getConfig()->get('Bridges.Apc', self::$library);
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

}