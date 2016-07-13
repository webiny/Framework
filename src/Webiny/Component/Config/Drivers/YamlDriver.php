<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Config\Drivers;

use Webiny\Component\Config\Bridge\Yaml\Yaml;
use Webiny\Component\Config\Bridge\Yaml\YamlInterface;
use Webiny\Component\Config\ConfigException;

/**
 * YamlDriver is responsible for parsing Yaml config files and returning a config array.
 *
 * @package   Webiny\Component\Config\Drivers;
 */
class YamlDriver extends AbstractDriver
{
    private $indent = 4;
    /**
     * @var null|YamlInterface
     */
    private $yaml = null;

    public function __construct($resource = null)
    {
        parent::__construct($resource);
        $this->yaml = Yaml::getInstance();
    }

    /**
     * Set Yaml indent
     *
     * @param int $indent
     *
     * @throws ConfigException
     * @return $this
     */
    public function setIndent($indent)
    {
        if (!$this->isNumber($indent)) {
            throw new ConfigException(ConfigException::MSG_INVALID_ARG, [
                                                                          '$indent',
                                                                          'integer'
                                                                      ]
            );
        }
        $this->indent = $indent;

        return $this;
    }

    /**
     * Get config as Yaml string
     *
     * @return string
     */
    protected function getStringInternal()
    {
        return $this->yaml->setResource($this->resource)->getString($this->indent);
    }

    /**
     * Parse config resource and build config array
     *
     * @throws ConfigException
     * @return array Config data array
     */
    protected function getArrayInternal()
    {
        try {
            return $this->yaml->setResource($this->resource)->getArray();
        } catch (\Exception $e) {
            throw new ConfigException($e->getMessage());
        }
    }
}