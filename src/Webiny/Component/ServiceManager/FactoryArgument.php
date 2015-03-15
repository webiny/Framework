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
    private $value;
    private $arguments;
    private $static;

    /**
     * @param string $resource
     * @param array  $arguments If arguments are empty, it's a static call
     * @param bool   $static
     */
    public function __construct($resource, $arguments = [], $static = true)
    {
        $this->value = $this->str($resource);
        $this->arguments = $this->arr($arguments);
        $this->static = $static;
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
        foreach ($this->arguments as $arg) {
            $arguments[] = $arg->value();
        }
        $this->arguments = $arguments;

        if ($this->value->startsWith('@')) {
            // Service can only be called in a NON-STATIC context
            $arguments = $this->arr($this->arguments)->count() > 0 ? $this->arguments : null;

            return ServiceManager::getInstance()->getService($this->value->val(), $arguments);
        } else {
            // CLASS can be instantiated or called statically
            $value = $this->value->val();
            if (class_exists($value) && !$this->static) {
                $reflection = new \ReflectionClass($value);

                return $reflection->newInstanceArgs($this->arguments);
            } elseif (class_exists($value) && $this->static) {
                // Return class name for static call
                return $this->value->val();
            }
            throw new ServiceManagerException(ServiceManagerException::SERVICE_CLASS_DOES_NOT_EXIST, [$this->value]);
        }
    }
}