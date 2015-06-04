<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Rest\Compiler\CacheDrivers;


class ArrayDriver implements CacheDriverInterface
{

    /**
     * @var array Internal array that holds the compiled cache.
     */
    protected $cache;


    /**
     * Save the compiled cache array.
     *
     * @param $api Name of the API.
     * @param $class Name of the class.
     * @param $version Version of the class.
     * @param $cacheArray The compiled class cache array.
     */
    public function save($api, $class, $version, $cacheArray)
    {
        $cacheKey = md5($api . $class);

        if (!isset($this->cache[$cacheKey])) {
            $this->cache[$cacheKey] = [
                'api'     => $api,
                'class'   => $class,
                'version' => []
            ];
        }

        $this->cache[$cacheKey]['version'][$version] = $cacheArray;
    }

    /**
     * Read the compiled cache array.
     *
     * @param $api Name of the API.
     * @param $class Name of the class.
     * @param $version Version of the class.
     *
     * @return bool|array Returns the compiled cache array, or false if cache is not found.
     */
    public function read($api, $class, $version)
    {
        $cacheKey = md5($api . $class);

        if (isset($this->cache[$cacheKey]) && isset($this->cache[$cacheKey]['version'][$version])) {
            return $this->cache[$cacheKey]['version'][$version];
        }

        return false;
    }

    /**
     * Delete the cache for the given api and class.
     *
     * @param $api Name of the API.
     * @param $class Name of the class.
     */
    public function delete($api, $class)
    {
        $cacheKey = md5($api . $class);

        if (isset($this->cache[$cacheKey])) {
            unset($this->cache[$cacheKey]);
        }
    }

    /**
     * Checks if the cache is still fresh based on the given ttl.
     *
     * @param $api Name of the API.
     * @param $class Name of the class.
     * @param $version Version of the class.
     * @param $ttl Unix timestamp against which the cache ttl should be compared.
     *
     * @return bool Returns true if cache is till fresh, otherwise false.
     */
    public function isFresh($api, $class, $version, $ttl)
    {
        if($this->read($api, $class, $version)){
            return true;
        }

        return false;
    }
}