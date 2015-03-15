<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\ServiceManager;

use Webiny\Component\StdLib\StdLibTrait;

/**
 * ServiceConfig class contains compiled service config and serves as a parameter for ServiceCreator when constructing services.
 *
 * @package         Webiny\Component\ServiceManager
 */
class ServiceConfig
{
    use StdLibTrait;

    private $class = null;
    private $arguments = null;
    private $calls = null;
    private $scope = ServiceScope::CONTAINER;
    private $factory = null;
    private $static = true;
    private $method = null;
    private $methodArguments = null;

    /**
     * @param null $calls
     */
    public function setCalls($calls)
    {
        $this->calls = $calls;
    }

    /**
     * @param null $class
     */
    public function setClass($class)
    {
        $this->class = $class;
    }

    /**
     * @param null $factory
     */
    public function setFactory($factory)
    {
        $this->factory = $factory;
    }

    /**
     * @return null|FactoryArgument
     */
    public function getFactory()
    {
        return $this->factory;
    }

    /**
     * @param null $method
     */
    public function setMethod($method)
    {
        $this->method = $method;
    }

    /**
     * @return null
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param null $methodArguments
     */
    public function setMethodArguments($methodArguments)
    {
        $this->methodArguments = $methodArguments;
    }

    /**
     * @return null
     */
    public function getMethodArguments()
    {
        return $this->methodArguments;
    }

    /**
     * @param boolean $static
     */
    public function setStatic($static)
    {
        $this->static = $static;
    }

    /**
     * @return boolean
     */
    public function getStatic()
    {
        return $this->static;
    }

    /**
     * @param null $arguments
     */
    public function setArguments($arguments)
    {
        $this->arguments = $arguments;
    }

    /**
     * @return mixed
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * @param string $scope
     */
    public function setScope($scope)
    {
        if ($this->isNull($scope) || !ServiceScope::exists($scope)) {
            $scope = ServiceScope::CONTAINER;
        }
        $this->scope = $scope;
    }

    /**
     * @return mixed
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * @return mixed
     */
    public function getCalls()
    {
        return $this->calls;
    }

    /**
     * @return mixed
     */
    public function getClass()
    {
        return $this->class;
    }
}