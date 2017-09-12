<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Rest\Compiler\CacheDrivers;

/**
 * Interface CacheDriverInterface
 * @package Webiny\Component\Rest\Compiler
 */
interface CacheDriverInterface
{
    /**
     * Save the compiled cache array.
     *
     * @param $api Name of the API.
     * @param $class Name of the class.
     * @param $version Version of the class.
     * @param $cacheArray The compiled class cache array.
     */
    public function save($api, $class, $version, $cacheArray);

    /**
     * Read the compiled cache array.
     *
     * @param string $api Name of the API.
     * @param string $class Name of the class.
     * @param string $version Version of the class.
     *
     * @return bool|array Returns the compiled cache array, or false if cache is not found.
     */
    public function read($api, $class, $version);

    /**
     * Delete the cache for the given api and class.
     *
     * @param $api Name of the API.
     * @param $class Name of the class.
     */
    public function delete($api, $class);

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
    public function isFresh($api, $class, $version, $ttl);
}