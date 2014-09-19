<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @link      http://www.webiny.com/wf-snv for the canonical source repository
 * @copyright Copyright @ 2009-2013 Webiny LTD. (http://www.webiny.com)
 * @license   http://www.webiny.com/framework/license
 */

namespace Webiny\Component\Config;

use Webiny\Component\Config\Drivers\DriverAbstract;
use Webiny\Component\Config\Drivers\IniDriver;
use Webiny\Component\Config\Drivers\JsonDriver;
use Webiny\Component\Config\Drivers\PhpDriver;
use Webiny\Component\Config\Drivers\YamlDriver;
use Webiny\Component\StdLib\SingletonTrait;
use Webiny\Component\StdLib\StdLibTrait;
use Webiny\Component\StdLib\StdObject\ArrayObject\ArrayObject;

/**
 * Config class creates config objects from files, strings and arrays.
 *
 * Example usage:
 * $config = \Webiny\Components\Config\Config::getInstance()->ini('path/to/file.ini');
 *
 * @package         Webiny\Component\Config
 */
class Config
{
    use StdLibTrait, SingletonTrait, CacheTrait;

    /**
     * Get Config object from INI file or string
     *
     * @param string $resource      Config resource in form of a file path or config string
     *
     * @param bool   $flushCache    Flush existing cache and load config file
     *
     * @param bool   $useSections   Default: true
     * @param string $nestDelimiter Delimiter for nested properties, ex: a.b.c or a-b-c
     *
     * @return ConfigObject
     */
    public function ini($resource, $flushCache = false, $useSections = true, $nestDelimiter = '.')
    {
        $config = ConfigCache::getCache($resource);
        if ($flushCache || !$config) {
            $driver = new IniDriver($resource);
            $driver->setDelimiter($nestDelimiter)->useSections($useSections);

            return new ConfigObject($driver);
        }

        return $config;
    }

    /**
     * Get Config object from JSON file or string
     *
     * @param string $resource   Config resource in form of a file path or config string
     *
     * @param bool   $flushCache Flush existing cache and load config file
     *
     * @return ConfigObject
     */
    public function json($resource, $flushCache = false)
    {
        $config = ConfigCache::getCache($resource);
        if ($flushCache || !$config) {
            return new ConfigObject(new JsonDriver($resource));
        }

        return $config;

    }

    /**
     * Get ConfigObject from YAML file or string
     *
     * @param string $resource   Config resource in form of a file path or config string
     *
     * @param bool   $flushCache Flush existing cache and load config file
     *
     * @return ConfigObject
     */
    public function yaml($resource, $flushCache = false)
    {
        $config = ConfigCache::getCache($resource);
        if ($flushCache || !$config) {
            return new ConfigObject(new YamlDriver($resource));
        }

        return $config;
    }


    /**
     * Get Config object from PHP file or array
     *
     * @param string|array $resource   Config resource in form of a file path or config string
     *
     * @param bool         $flushCache Flush existing cache and load config file
     *
     * @return ConfigObject
     */
    public function php($resource, $flushCache = false)
    {
        $config = ConfigCache::getCache($resource);
        if ($flushCache || !$config) {
            return new ConfigObject(new PhpDriver($resource));
        }

        return $config;
    }

    /**
     * Parse resource and create a Config object
     * A valid resource is a PHP array, ArrayObject or an instance of DriverAbstract
     *
     * @param array|ArrayObject|DriverAbstract $resource   Config resource
     * @param bool                             $flushCache Flush existing cache and load config file
     *
     * @return ConfigObject
     */
    public function parseResource($resource, $flushCache = false)
    {
        $driver = $resource;
        $driverAbstractClassName = '\Webiny\Component\Config\Drivers\DriverAbstract';
        if (self::isInstanceOf($resource, $driverAbstractClassName)) {
            $resource = $resource->getResource();
        }

        $cache = ConfigCache::getCache($resource);
        if ($flushCache || !$cache) {
            return new ConfigObject($driver);
        }

        return $cache;
    }
}