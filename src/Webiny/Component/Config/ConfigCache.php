<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Config;

use Webiny\Component\StdLib\StdLibTrait;
use Webiny\Component\StdLib\StdObject\StdObjectWrapper;

/**
 * ConfigCache class holds caches data and holds info about original resource.
 *
 * @package         Webiny\Component\Config
 */
class ConfigCache
{
    use StdLibTrait;

    private static $_configCache = null;

    /**
     * Get config from cache
     *
     * @param mixed $resource
     *
     * @return ConfigObject
     */
    public static function getCache($resource)
    {
        if (!self::isArrayObject(self::$_configCache)) {
            self::$_configCache = self::arr(self::$_configCache);
        }

        $cacheKey = !self::_isMd5($resource) ? self::createCacheKey($resource) : $resource;

        if (self::$_configCache->keyExists($cacheKey)) {
            return self::$_configCache->key($cacheKey);
        }

        return false;
    }

    /**
     * Set config cache
     *
     * @param string       $cacheKey
     * @param ConfigObject $config
     */
    public static function setCache($cacheKey, $config)
    {
        if (!self::isArrayObject(self::$_configCache)) {
            self::$_configCache = self::arr(self::$_configCache);
        }
        self::$_configCache->key($cacheKey, $config);
    }

    /**
     * Create cache key for storing ConfigObject
     *
     * @param $resource
     *
     * @return mixed
     */
    public static function createCacheKey($resource)
    {
        $resourceType = ConfigObject::determineResourceType($resource);
        switch ($resourceType) {
            case ConfigObject::ARRAY_RESOURCE:
                return self::str(json_encode($resource))->md5()->val();
            case ConfigObject::STRING_RESOURCE:
                return self::str($resource)->md5()->val();
            // Default means it's a ConfigObject::FILE_RESOURCE
            default:
                return self::str(StdObjectWrapper::toString($resource))->md5()->val();
        }
    }


    /**
     * Checks if md5 string is valid
     *
     * @param String $md5
     *
     * @return Boolean
     */
    private static function _isMd5($md5)
    {
        if (!self::isString($md5) && !self::isStringObjecT($md5)) {
            return false;
        }

        $md5 = StdObjectWrapper::toString($md5);

        return !empty($md5) && preg_match('/^[a-f0-9]{32}$/', $md5);
    }

}