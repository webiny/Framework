<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Rest\Compiler;

use Webiny\Component\Rest\Compiler\CacheDrivers\CacheDriverInterface;
use Webiny\Component\Rest\RestException;

/**
 * This cache verifies the cache files and can also return the content from an existing cache file.
 *
 * @package         Webiny\Component\Rest\Compiler
 */
class Cache
{
    /**
     * @var CacheDriverInterface
     */
    private $cacheDriver;


    /**
     * Base constructor.
     *
     * @param CacheDriverInterface $cacheDriver
     */
    public function __construct(CacheDriverInterface $cacheDriver)
    {
        $this->cacheDriver = $cacheDriver;
    }

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
    public function isCacheValid($api, $class)
    {
        // get the modified time of the $class
        try {
            $reflection = new \ReflectionClass($class);
        } catch (\Exception $e) {
            throw new RestException('Unable to validate the cache for ' . $class . '. ' . $e->getMessage());
        }

        $classModTime = filemtime($reflection->getFileName());

        return $this->cacheDriver->isFresh($api, $class, 'current', $classModTime);
    }

    /**
     * Returns the contents of an existing cache file in form of an array.
     *
     * @param $api     Name of the API.
     * @param $class   Name of the class.
     * @param $version Version of the class.
     *
     * @return array
     * @throws \Webiny\Component\Rest\RestException
     */
    public function getCacheContent($api, $class, $version)
    {
        return $this->cacheDriver->read($api, $class, $version);
    }

    /**
     * @param $api   Name of the API.
     * @param $class Name of the class.
     */
    public function deleteCache($api, $class)
    {
        $this->cacheDriver->delete($api, $class);
    }

    /**
     * Save the compiled cache array.
     *
     * @param $api        Name of the API.
     * @param $class      Name of the class.
     * @param $version    Version of the class.
     * @param $cacheArray The compiled class cache array.
     */
    public function writeCacheFile($api, $class, $version, $cacheArray)
    {
        $this->cacheDriver->save($api, $class, $version, $cacheArray);
    }

}