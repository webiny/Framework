<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\ServiceManager;

use Webiny\Component\StdLib\StdLibTrait;

/**
 * FactoryArgument class is responsible for handling factory services. It is different from Argument class
 * in that it differentiates static and non-static calls to your 'factory' parameter and it only supports
 * handling of services and classes.
 *
 * @package         Webiny\Component\ServiceManager
 */
class FactoryArgument
{
    use StdLibTrait;

    /**
     * Simple value, class name or service name
     */
    private $_value;
    private $_arguments;
    private $_static;

    /**
     * @param string $resource
     * @param array  $arguments If arguments are empty, it's a static call
     * @param bool   $static
     */
    public function __construct($resource, $arguments = [], $static = true)
    {
        $this->_value = $this->str($resource);
        $this->_arguments = $this->arr($arguments);
        $this->_static = $static;
    }

    /**
     * Get real FactoryArgument value
     * @throws ServiceManagerException
     *
     * @return mixed
     */
    public function value()
    {

        // Get real arguments values from Argument instances
        $arguments = [];
        foreach ($this->_arguments as $arg) {
            $arguments[] = $arg->value();
        }
        $this->_arguments = $arguments;

        if ($this->_value->startsWith('@')) {
            // Service can only be called in a NON-STATIC context
            $arguments = $this->arr($this->_arguments)->count() > 0 ? $this->_arguments : null;

            return ServiceManager::getInstance()->getService($this->_value->val(), $arguments);
        } else {
            // CLASS can be instantiated or called statically
            $value = $this->_value->val();
            if (class_exists($value) && !$this->_static) {
                $reflection = new \ReflectionClass($value);

                return $reflection->newInstanceArgs($this->_arguments);
            } elseif (class_exists($value) && $this->_static) {
                // Return class name for static call
                return $this->_value->val();
            }
            throw new ServiceManagerException(ServiceManagerException::SERVICE_CLASS_DOES_NOT_EXIST, [$this->_value]);
        }
    }
}