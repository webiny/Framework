<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Config\Drivers;

use Webiny\Component\Config\ConfigException;
use Webiny\Component\StdLib\Exception\Exception;
use Webiny\Component\StdLib\StdObject\StdObjectException;
use Webiny\Component\StdLib\StdObject\StdObjectWrapper;
use Webiny\Component\StdLib\StdObject\StringObject\StringObject;
use Webiny\Component\StdLib\ValidatorTrait;

/**
 * JsonDriver is responsible for parsing JSON config files and returning a config array.
 *
 * @package   Webiny\Component\Config\Drivers;
 */
class JsonDriver extends DriverAbstract
{
    /**
     * Get config data as string
     *
     * @return string
     */
    protected function _getString()
    {
        return json_encode($this->_getArray());
    }

    /**
     * Parse config resource and build config array
     * @return array|ArrayObject
     */
    protected function _getArray()
    {
        if ($this->isArray($this->_resource)) {
            return $this->_resource;
        }

        if (file_exists($this->_resource)) {
            $config = file_get_contents($this->_resource);
        } else {
            $config = $this->_resource;
        }

        return $this->_parseJsonString($config);
    }

    /**
     * Parse JSON string and return config array
     *
     * @param array $data
     *
     * @throws ConfigException
     * @return array
     */
    private function _parseJsonString($data)
    {
        try {
            return json_decode($data, true);
        } catch (Exception $e) {
            throw new ConfigException($e->getMessage());
        }
    }
}