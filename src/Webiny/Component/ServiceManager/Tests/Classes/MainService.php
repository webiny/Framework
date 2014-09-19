<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\ServiceManager\Tests\Classes;


class MainService
{
    private $_value;
    private $_firstArgument;
    private $_injectedService;

    /**
     * @var ConstructorArgumentClass
     */
    private $_someInstance;

    public function __construct($simpleArgument, $secondService, ConstructorArgumentClass $someInstance)
    {
        $this->_firstArgument = $simpleArgument;
        $this->_injectedService = $secondService;
        $this->_someInstance = $someInstance;
    }

    public function setCallValue($value)
    {
        $this->_value = $value;
    }

    public function getCallValue()
    {
        return $this->_value;
    }

    public function getFirstArgumentValue()
    {
        return $this->_firstArgument;
    }

    public function getInjectedServiceValue()
    {
        return $this->_injectedService;
    }

    /**
     * @return ConstructorArgumentClass
     */
    public function getSomeInstance()
    {
        return $this->_someInstance;
    }
}