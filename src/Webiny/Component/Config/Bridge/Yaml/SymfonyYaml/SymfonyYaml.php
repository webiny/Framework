<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Config\Bridge\Yaml\SymfonyYaml;

use Symfony\Component\Yaml\Yaml;
use Webiny\Component\Config\Bridge\Yaml\Spyc\SymfonyYamlException;
use Webiny\Component\Config\Bridge\Yaml\YamlInterface;
use Webiny\Component\StdLib\StdLibTrait;
use Webiny\Component\StdLib\StdObject\StdObjectWrapper;

/**
 * Bridge for Symfony Yaml parser
 *
 * @package   Webiny\Component\Config\Bridge\Yaml\SymfonyYaml
 */
class SymfonyYaml implements YamlInterface
{
    use StdLibTrait;

    private $_indent = 4;
    private $_resource = null;

    /**
     * Set resource to work on
     *
     * @param $resource
     *
     * @return $this
     */
    public function setResource($resource)
    {
        $this->_resource = $resource;

        return $this;
    }

    /**
     * Get Yaml value as string
     *
     * @param int $indent
     *
     * @return string Yaml string
     */
    function getString($indent = 4)
    {
        $this->_indent = $indent;

        return $this->_toString();
    }

    /**
     * Get Yaml value as array
     *
     * @return array Parsed Yaml array
     */
    function getArray()
    {
        return $this->_parseResource();
    }

    /**
     * Parse given Yaml resource and build array
     * This method must support file paths (string or StringObject) and FileObject
     *
     * @throws SymfonyYamlException
     * @return string
     */
    private function _parseResource()
    {
        if ($this->isArray($this->_resource)) {
            return StdObjectWrapper::toArray($this->_resource);
        } elseif ($this->isString($this->_resource)) {
            return Yaml::parse($this->_resource);
        } elseif ($this->isInstanceOf($this->_resource, 'Webiny\Component\Config\ConfigObject')) {
            return $this->_resource->toArray();
        }

        throw new SymfonyYamlException(SymfonyYamlException::UNABLE_TO_PARSE, [gettype($this->_resource)]);
    }

    /**
     * Convert given data to Yaml string
     *
     * @throws SymfonyYamlException
     * @return $this
     */
    private function _toString()
    {
        return Yaml::dump($this->_parseResource(), 2, $this->_indent);
    }

}