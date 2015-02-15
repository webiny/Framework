<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Config\Drivers;

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
    protected function _getString()
    {
        return "<?php\n" . "return " . var_export($this->_getArray(), true) . ";\n";
    }


    /**
     * Parse config resource and build config array
     * @return array
     */
    protected function _getArray()
    {
        if ($this->isArray($this->_resource)) {
            return $this->_resource;
        }

        $resource = $this->str($this->_resource);
        $resource->replace([
                               '<?php',
                               '<?'
                           ], ''
        );

        return eval($resource);
    }
}