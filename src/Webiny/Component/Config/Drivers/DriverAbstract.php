<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Config\Drivers;

use Webiny\Component\Config\ConfigException;
use Webiny\Component\StdLib\StdLibTrait;
use Webiny\Component\StdLib\StdObject\ArrayObject\ArrayObject;
use Webiny\Component\StdLib\StdObject\StdObjectWrapper;
use Webiny\Component\StdLib\StdObject\StringObject\StringObject;

/**
 * Abstract Driver class
 *
 * @package   Webiny\Component\Config\Drivers;
 */
abstract class DriverAbstract
{
    use StdLibTrait;

    /**
     * Contains config data which needs to be parsed and converted to ConfigObject
     * @var null|string|array Resource given to config driver
     */
    protected $_resource = null;

    /**
     * Get config data as string
     *
     * @return string Formatted config data
     */
    abstract protected function _getString();

    /**
     * Parse config resource and build config array
     * @return array|ArrayObject Config data
     */
    abstract protected function _getArray();

    /**
     * Create config driver instance
     *
     * @param null $resource Resource for driver
     */
    public function __construct($resource = null)
    {
        $this->_resource = $this->_normalizeResource($resource);
    }

    /**
     * Get formatted config data as string
     *
     * @throws ConfigException
     * @return string Formatted config data
     */
    final public function getString()
    {
        $this->_validateResource();

        $res = $this->_getString();
        if (!$this->isString($res) && !$this->isStringObject($res)) {
            throw new ConfigException('DriverAbstract method _getString() must return string or StringObject.');
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

        try {
            $this->_validateResource();
        } catch (StdObjectException $e) {
            throw new ConfigException($e->getMessage());
        }

        $res = $this->_getArray();
        if (!$this->isArray($res) && !$this->isArrayObject($res)) {
            throw new ConfigException('DriverAbstract method _getArray() must return array or ArrayObject.');
        }

        return StdObjectWrapper::toArray($res);
    }

    /**
     * Get driver resource
     * @return mixed Driver resource (can be: string, array, StringObject, ArrayObject, FileObject)
     */
    final public function getResource()
    {
        return $this->_resource;
    }

    /**
     * Set driver resource
     *
     * @param mixed $resource Driver resource (can be: string, array, StringObject, ArrayObject, FileObject)
     *
     * @return $this
     */
    final public function setResource($resource)
    {
        $this->_resource = $this->_normalizeResource($resource);

        return $this;
    }

    /**
     * Write config to destination
     *
     * @param null|string|StringObject|FileObject $destination
     *
     * @throws \InvalidArgumentException
     * @throws ConfigException
     * @return $this
     */
    final public function saveToFile($destination = null)
    {

        if ($this->isString($destination) || $this->isStringObject($destination)) {
            $destination = StdObjectWrapper::toString($destination);
        }

        if ($this->isNull($destination)) {
            if ($this->isNull($this->_resource)) {
                throw new ConfigException('No valid resource was found to use as config target file! Specify a $destination argument or load your Config using a file resource!'
                );
            } else {
                $destination = $this->_resource;
            }
        }

        if (file_put_contents($destination, $this->_getString())) {
            return $this;
        }

        throw new ConfigException(ConfigException::COULD_NOT_SAVE_CONFIG_FILE);
    }

    /**
     * Validate given config resource and throw ConfigException if it's not valid
     * @throws ConfigException
     */
    protected function _validateResource()
    {
        if (self::isNull($this->_resource)) {
            throw new ConfigException('Config resource can not be NULL! Please provide a valid file path, config string or PHP array.'
            );
        }

        if ($this->isArray($this->_resource)) {
            return true;
        }

        // @TODO: possibility to load config from relative path (debug backtrace parsing?)

        // Check if it's a valid file path
        if ((dirname($this->_resource) != '.' && !file_exists($this->_resource)) || dirname($this->_resource) == '.') {
            throw new ConfigException('Config resource file does not exist!');
        }

        // Perform string checks
        $this->_resource = $this->str($this->_resource);
        if ($this->_resource->trim()->length() == 0) {
            throw new ConfigException('Config resource string can not be empty! Please provide a valid file path, config string or PHP array.'
            );
        }
    }

    /**
     * Normalize resource value
     *
     * @param mixed $resource
     *
     * @return mixed Normalized resource value
     */
    private function _normalizeResource($resource)
    {
        // Convert resource to native PHP type
        if ($this->isStdObject($resource)) {
            return $resource->val();
        }

        return $resource;
    }

}