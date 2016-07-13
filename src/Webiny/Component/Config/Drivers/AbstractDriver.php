<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @link      http://www.webiny.com/wf-snv for the canonical source repository
 * @copyright Copyright (c) 2009-2013 Webiny LTD. (http://www.webiny.com)
 * @license   http://www.webiny.com/framework/license
 */

namespace Webiny\Component\Config\Drivers;

use Webiny\Component\Config\ConfigException;
use Webiny\Component\StdLib\StdLibTrait;
use Webiny\Component\StdLib\StdObject\ArrayObject\ArrayObject;
use Webiny\Component\StdLib\StdObject\StdObjectException;
use Webiny\Component\StdLib\StdObject\StdObjectWrapper;
use Webiny\Component\StdLib\StdObject\StringObject\StringObject;
use Webiny\Component\StdLib\StdObject\StringObject\StringObjectException;

/**
 * Abstract Driver class
 *
 * @package   Webiny\Component\Config\Drivers;
 */
abstract class AbstractDriver
{
    use StdLibTrait;

    /**
     * Contains config data which needs to be parsed and converted to ConfigObject
     * @var null|string|array Resource given to config driver
     */
    protected $resource = null;

    /**
     * Get config data as string
     *
     * @return string Formatted config data
     */
    abstract protected function getStringInternal();

    /**
     * Parse config resource and build config array
     * @return array|ArrayObject Config data
     */
    abstract protected function getArrayInternal();

    /**
     * Create config driver instance
     *
     * @param null $resource Resource for driver
     *
     * @throws ConfigException
     * @throws StringObjectException
     */
    public function __construct($resource = null)
    {
        $this->resource = $resource;

        if (self::isNull($this->resource) || !$this->resource) {
            throw new ConfigException('Config resource can not be NULL or FALSE! Please provide a valid file path, config string or PHP array.');
        }

        if ($this->isStdObject($resource)) {
            $this->resource = $resource->val();
        }

        if ($this->isArray($this->resource)) {
            return;
        }

        /**
         * Perform string checks
         */
        if ($this->str($this->resource)->trim()->length() == 0) {
            throw new ConfigException('Config resource string can not be empty! Please provide a valid file path, config string or PHP array.');
        }

        /**
         * If it's a file - get its contents
         */
        if ($this->isFilepath($this->resource)) {
            if (!$this->isFile($this->resource)) {
                throw new ConfigException('Invalid config file path given: ' . $this->resource);
            }
            $path = dirname($this->resource);
            $this->resource = file_get_contents($this->resource);
            $this->resource = str_replace('__DIR__', $path, $this->resource);
        }
    }

    /**
     * Get formatted config data as string
     *
     * @throws ConfigException
     * @return string Formatted config data
     */
    final public function getString()
    {
        $res = $this->getStringInternal();
        if (!$this->isString($res) && !$this->isStringObject($res)) {
            throw new ConfigException('AbstractDriver method _getString() must return string or StringObject.');
        }

        return StdObjectWrapper::toString($res);
    }

    /**
     * Get config data as array
     *
     * @throws ConfigException
     * @return array Parsed resource data array
     */
    final public function getArray()
    {
        $res = $this->getArrayInternal();
        if (!$this->isArray($res) && !$this->isArrayObject($res)) {
            $errorMessage = 'AbstractDriver method _getArray() must return array or ArrayObject.';
            $errorMessage .= ' Make sure you have provided a valid config file path with file extension.';
            throw new ConfigException($errorMessage);
        }

        return StdObjectWrapper::toArray($res);
    }

    /**
     * Get driver resource
     * @return mixed Driver resource (can be: string, array, StringObject, ArrayObject)
     */
    final public function getResource()

    {
        return $this->resource;
    }

    private function isFilepath($string)
    {
        if (!$this->isString($string)) {
            return false;
        }

        if (preg_match('/^([\w+-\/]+\.[a-z]{3,4}|[\w+]:\\[\w+-\\]+\.[a-z]{3,4})$/m', $string)) {
            return true;
        }

        return false;
    }
}