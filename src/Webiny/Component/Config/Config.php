<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Config;

use Webiny\Component\Config\Drivers\AbstractDriver;
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
 * Ex1: $config = \Webiny\Components\Config\Config::getInstance()->ini('path/to/file.ini');
 * Ex2: $config = $this->config()->ini('path/to/file.ini');
 *
 * @package         Webiny\Component\Config
 */
class Config
{
    use StdLibTrait, SingletonTrait;

    /**
     * Get Config object from INI file or string
     *
     * @param string $resource Config resource in form of a file path or config string
     * @param bool   $useSections Default: true
     * @param string $nestDelimiter Delimiter for nested properties, ex: a.b.c or a-b-c
     *
     * @return ConfigObject
     */
    public function ini($resource, $useSections = true, $nestDelimiter = '.')
    {
        $driver = new IniDriver($resource);
        $driver->setDelimiter($nestDelimiter)->useSections($useSections);

        return new ConfigObject($driver);
    }

    /**
     * Get Config object from JSON file or string
     *
     * @param string $resource Config resource in form of a file path or config string
     *
     * @return ConfigObject
     */
    public function json($resource)
    {
        return new ConfigObject(new JsonDriver($resource));
    }

    /**
     * Get ConfigObject from YAML file or string
     *
     * @param string $resource Config resource in form of a file path or config string
     *
     * @return ConfigObject
     */
    public function yaml($resource)
    {
        return new ConfigObject(new YamlDriver($resource));
    }


    /**
     * Get Config object from PHP array
     *
     * @param array $resource Config resource in form of a PHP array
     *
     * @return ConfigObject
     */
    public function php($resource)
    {
        return new ConfigObject(new PhpDriver($resource));
    }

    /**
     * Parse resource and create a Config object
     * A valid resource is a PHP array, ArrayObject or an instance of AbstractDriver
     *
     * @param array|ArrayObject|AbstractDriver $resource Config resource
     *
     * @return ConfigObject
     */
    public function parseResource($resource)
    {
        return new ConfigObject($resource);
    }
}