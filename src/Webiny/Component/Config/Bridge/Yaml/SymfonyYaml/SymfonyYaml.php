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
use Webiny\Component\Config\ConfigObject;
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

    private $indent = 4;
    private $resource = null;

    /**
     * Set resource to work on
     *
     * @param $resource
     *
     * @return $this
     */
    public function setResource($resource)
    {
        $this->resource = $resource;

        return $this;
    }

    /**
     * Get Yaml value as string
     *
     * @param int $indent
     *
     * @return string Yaml string
     */
    public function getString($indent = 4)
    {
        $this->indent = $indent;

        return $this->toString();
    }

    /**
     * Get Yaml value as array
     *
     * @return array Parsed Yaml array
     */
    public function getArray()
    {
        return $this->parseResource();
    }

    /**
     * Parse given Yaml resource and build array
     *
     * @throws SymfonyYamlException
     * @return array
     */
    private function parseResource()
    {
        if ($this->isArray($this->resource)) {
            return StdObjectWrapper::toArray($this->resource);
        } elseif ($this->isString($this->resource)) {
            return Yaml::parse($this->resource);
        } elseif ($this->isInstanceOf($this->resource, ConfigObject::class)) {
            return $this->resource->toArray();
        }

        throw new SymfonyYamlException(SymfonyYamlException::UNABLE_TO_PARSE, [gettype($this->resource)]);
    }

    /**
     * Convert given data to Yaml string
     *
     * @throws SymfonyYamlException
     * @return string
     */
    private function toString()
    {
        return Yaml::dump($this->parseResource(), 2, $this->indent);
    }

}