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

    private $_class = null;
    private $_arguments = null;
    private $_calls = null;
    private $_scope = ServiceScope::CONTAINER;
    private $_factory = null;
    private $_static = true;
    private $_method = null;
    private $_methodArguments = null;

    /**
     * @param null $calls
     */
    public function setCalls($calls)
    {
        $this->_calls = $calls;
    }

    /**
     * @param null $class
     */
    public function setClass($class)
    {
        $this->_class = $class;
    }

    /**
     * @param null $factory
     */
    public function setFactory($factory)
    {
        $this->_factory = $factory;
    }

    /**
     * @return null|FactoryArgument
     */
    public function getFactory()
    {
        return $this->_factory;
    }

    /**
     * @param null $method
     */
    public function setMethod($method)
    {
        $this->_method = $method;
    }

    /**
     * @return null
     */
    public function getMethod()
    {
        return $this->_method;
    }

    /**
     * @param null $methodArguments
     */
    public function setMethodArguments($methodArguments)
    {
        $this->_methodArguments = $methodArguments;
    }

    /**
     * @return null
     */
    public function getMethodArguments()
    {
        return $this->_methodArguments;
    }

    /**
     * @param boolean $static
     */
    public function setStatic($static)
    {
        $this->_static = $static;
    }

    /**
     * @return boolean
     */
    public function getStatic()
    {
        return $this->_static;
    }

    /**
     * @param null $arguments
     */
    public function setArguments($arguments)
    {
        $this->_arguments = $arguments;
    }

    /**
     * @return mixed
     */
    public function getArguments()
    {
        return $this->_arguments;
    }

    /**
     * @param string $scope
     */
    public function setScope($scope)
    {
        if ($this->isNull($scope) || !ServiceScope::exists($scope)) {
            $scope = ServiceScope::CONTAINER;
        }
        $this->_scope = $scope;
    }

    /**
     * @return mixed
     */
    public function getScope()
    {
        return $this->_scope;
    }

    /**
     * @return mixed
     */
    public function getCalls()
    {
        return $this->_calls;
    }

    /**
     * @return mixed
     */
    public function getClass()
    {
        return $this->_class;
    }
}