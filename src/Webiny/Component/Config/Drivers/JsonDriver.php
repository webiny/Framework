<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Config\Drivers;

use Webiny\Component\Config\ConfigException;
use Webiny\Component\StdLib\Exception\Exception;

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

        return $this->_parseJsonString($this->_resource);
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