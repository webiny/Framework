<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Config\Drivers;

use Webiny\Component\Config\ConfigException;

/**
 * PhpDriver is responsible for parsing PHP config files and returning a config array.
 *
 * @package   Webiny\Component\Config\Drivers;
 */
class PhpDriver extends DriverAbstract
{
    /**
     * Get config data as string
     *
     * @return string
     */
    protected function getStringInternal()
    {
        return "<?php\n" . "return " . var_export($this->getArray(), true) . ";\n";
    }


    /**
     * Parse config resource and build config array
     * @return array
     * @throws ConfigException
     */
    protected function getArrayInternal()
    {
        if ($this->isArray($this->resource)) {
            return $this->resource;
        }
        throw new ConfigException('PhpDriver can only work with array resources!');
    }
}