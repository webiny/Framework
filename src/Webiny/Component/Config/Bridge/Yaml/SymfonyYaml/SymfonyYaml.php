<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Config\Bridge\Yaml\SymfonyYaml;

use Symfony\Component\Yaml\Yaml;
use Webiny\Component\Config\Bridge\Yaml\YamlAbstract;
use Webiny\Component\Config\Bridge\Yaml\YamlInterface;
use Webiny\Component\Config\Config;
use Webiny\Component\StdLib\StdLibTrait;
use Webiny\Component\StdLib\StdObject\FileObject\FileObject;
use Webiny\Component\StdLib\StdObject\StdObjectAbstract;
use Webiny\Component\StdLib\StdObject\StdObjectException;
use Webiny\Component\StdLib\StdObject\StdObjectWrapper;
use Webiny\Component\StdLib\StdObject\StringObject\StringObject;
use Webiny\Component\StdLib\ValidatorTrait;

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
     * Write current Yaml data to file
     *
     * @param string|StringObject|FileObject $destination
     *
     * @throws SymfonyYamlException
     * @return bool
     */
    public function writeToFile($destination)
    {

        if (!$this->isString($destination) && !$this->isStringObject($destination) && !$this->isFileObject($destination
            )
        ) {
            throw new SymfonyYamlException('SymfonyYaml Bridge - destination argument must be a string, StringObject or FileObject!'
            );
        }

        try {
            $destination = $this->file($destination);
            $destination->truncate()->write($this->_toString());
        } catch (StdObjectException $e) {
            throw new SymfonyYamlException('SymfonyYaml Bridge - ' . $e->getMessage());
        }

        return true;
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
        if ($this->isArray($this->_resource) || $this->isArrayObject($this->_resource)) {
            return StdObjectWrapper::toArray($this->_resource);
        } elseif ($this->isFileObject($this->_resource)) {
            return Yaml::parse($this->_resource->val());
        } elseif ($this->isFile($this->_resource)) {
            return Yaml::parse($this->_resource);
        } elseif ($this->isString($this->_resource) || $this->isStringObject($this->_resource)) {
            return Yaml::parse(StdObjectWrapper::toString($this->_resource));
        } elseif ($this->isInstanceOf($this->_resource, 'Webiny\Component\Config\ConfigObject')) {
            return $this->_resource->toArray();
        }

        throw new SymfonyYamlException('SymfonyYaml Bridge - Unable to parse given resource of type %s', [
                gettype($this->_resource
                )
            ]
        );
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