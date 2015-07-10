<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Rest\Compiler\CacheDrivers;

use Webiny\Component\Rest\Rest;
use Webiny\Component\Rest\RestException;
use Webiny\Component\StdLib\StdLibTrait;

class FilesystemDriver implements CacheDriverInterface
{
    use StdLibTrait;

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
        // cache template
        $cacheFileTemplate = file_get_contents(__DIR__ . '/FilesystemTemplates/Cache.tpl');

        // export array
        $array = var_export($cacheArray, true);

        // build map based on template keys
        $map = [
            'export'    => $array,
            'class'     => $cacheArray['class'],
            'version'   => 'v' . $version,
            'buildDate' => date('D, d. M, Y H:i:s')
        ];

        $data = $cacheFileTemplate;
        foreach ($map as $k => $v) {
            $data = str_replace('|' . $k . '|', $v, $data);
        }

        $cacheFile = $this->getCacheFilename($api, $class, $version);
        file_put_contents($cacheFile, $data);
    }

    /**
     * Read the compiled cache array.
     *
     * @param $api     Name of the API.
     * @param $class   Name of the class.
     * @param $version Version of the class.
     *
     * @return array|bool Returns the compiled cache array, or false if cache is not found.
     * @throws RestException
     */
    public function read($api, $class, $version)
    {
        $cacheFile = $this->getCacheFilename($api, $class, $version);

        if (!file_exists($cacheFile)) {
            throw new RestException('Cache file doesn\'t exist: ' . $cacheFile);
        }

        return include $cacheFile;
    }

    /**
     * Delete the cache for the given api and class.
     *
     * @param $api   Name of the API.
     * @param $class Name of the class.
     */
    public function delete($api, $class)
    {
        $cacheFolder = $this->getCacheFolder($api, $class);

        array_map(function ($file) {
            $file = realpath($file);
            if (file_exists($file)) {
                unlink($file);
            }
        }, glob($cacheFolder . '*.*'));
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
        $cacheFile = $this->getCacheFilename($api, $class, $version);

        if (!file_exists($cacheFile)) {
            return false;
        }

        if (filemtime($cacheFile) < $ttl) {
            return false;
        }

        return true;
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
    private function getCacheFilename($api, $class, $version)
    {
        if (!is_numeric(substr($version, 0, 1))) {
            $versionFile = $version . '.php';
        } else {
            $versionFile = 'v' . $version . '.php';
        }

        // combine the paths and look for the cache file
        $cacheFile = $this->getCacheFolder($api, $class) . $versionFile;

        return $cacheFile;
    }

    /**
     * Returns the folder where the cache files should be stored.
     *
     * @param string $api     Name of the api configuration.
     * @param string $class   Class name.
     *
     * @return string
     * @throws RestException
     * @throws \Webiny\Component\Rest\RestException
     */
    private function getCacheFolder($api, $class)
    {
        // get the api compile folder
        $compilePath = Rest::getConfig()->get($api)->CompilePath;
        if (empty($compilePath)) {
            throw new RestException('You must set CompilePath for "' . $api . '" api.');
        }
        $apiFolder = $this->str($compilePath)->trimRight('/')->append(DIRECTORY_SEPARATOR . $api);

        // get class cache folder
        $classCacheFolder = $this->str($class)->trimLeft('\\')->replace('\\', '_')->val();

        $apiFolder = $apiFolder . DIRECTORY_SEPARATOR . $classCacheFolder;

        if (!is_dir($apiFolder)) {
            mkdir($apiFolder, 0755, true);
            clearstatcache(true, realpath($apiFolder));
        }

        return $apiFolder . DIRECTORY_SEPARATOR;
    }
}