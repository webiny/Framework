<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Rest\Compiler;

use Webiny\Component\Rest\Parser\PathTransformations;
use Webiny\Component\Rest\Rest;
use Webiny\Component\Rest\RestException;
use Webiny\Component\StdLib\StdLibTrait;

/**
 * This cache verifies the cache files and can also return the content from an existing cache file.
 *
 * @package         Webiny\Component\Rest\Compiler
 */
class Cache
{
    use StdLibTrait;

    /**
     * Checks if a cache file is valid.
     *
     * A valid file is considered and existing cache file that has a newer creation time, than the modify time,
     * of an api class that the cache file belongs to.
     *
     * @param string $api   Name of the rest api configuration.
     * @param string $class Fully qualified class name.
     *
     * @throws \Webiny\Component\Rest\RestException
     * @return bool True if cache file is valid, otherwise false.
     */
    public static function isCacheValid($api, $class)
    {
        // get modified time of the cached file (we look at "current" version)
        $cacheFilename = self::getCacheFilename($api, $class, 'current');
        if (!file_exists($cacheFilename)) {
            return false;
        }

        // get the modified time of the $class
        try {
            $reflection = new \ReflectionClass($class);
        } catch (\Exception $e) {
            throw new RestException('Unable to validate the cache for ' . $class . '. ' . $e->getMessage());
        }

        $classModTime = filemtime($reflection->getFileName());

        if (filemtime($cacheFilename) < $classModTime) {
            return false;
        }

        return true;
    }

    /**
     * Returns the contents of an existing cache file in form of an array.
     *
     * @param string $cacheFile Path to the cache file
     *
     * @return array
     * @throws \Webiny\Component\Rest\RestException
     */
    public static function getCacheContent($cacheFile)
    {
        if (!file_exists($cacheFile)) {
            throw new RestException('Cache file doesn\'t exist: ' . $cacheFile);
        }

        return include $cacheFile;
    }

    /**
     * Returns the name of the cached class file.
     *
     * @param string $api     Name of the api configuration.
     * @param string $class   Class name.
     * @param string $version Version of the api class.
     *
     * @return string Full path to the cache filename.
     * @throws \Webiny\Component\Rest\RestException
     */
    public static function getCacheFilename($api, $class, $version)
    {
        // get the api compile folder
        $compilePath = Rest::getConfig()->get($api)->CompilePath;
        if (empty($compilePath)) {
            throw new RestException('You must set CompilePath for "' . $api . '" api.');
        }
        $apiFolder = self::str($compilePath)->trimRight('/')->append(DIRECTORY_SEPARATOR . $api);

        // get class cache folder
        $classCacheFolder = PathTransformations::classCacheFolder($class);

        $apiFolder = $apiFolder . DIRECTORY_SEPARATOR . $classCacheFolder;
        if (!is_dir($apiFolder)) {
            mkdir($apiFolder, 0755, true);
        }

        $versionFile = PathTransformations::versionCacheFilename($version);

        // combine the paths and look for the cache file
        $cacheFile = $apiFolder . DIRECTORY_SEPARATOR . $versionFile;

        return $cacheFile;
    }
}