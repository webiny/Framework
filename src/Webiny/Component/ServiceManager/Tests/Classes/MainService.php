<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\ServiceManager\Tests\Classes;


class MainService
{
    private $value;
    private $firstArgument;
    private $instanceService;
    private $factoryService;

    /**
     * @var ConstructorArgumentClass
     */
    private $someInstance;

    public function __construct($simpleArgument, $factoryService, InstanceService $instanceService, ConstructorArgumentClass $someInstance)
    {
        $this->firstArgument = $simpleArgument;
        $this->factoryService = $factoryService;
        $this->instanceService = $instanceService;
        $this->someInstance = $someInstance;
    }

    public function setCallValue($value)
    {
        $this->value = $value;
    }

    public function getCallValue()
    {
        return $this->value;
    }

    public function getFirstArgumentValue()
    {
        return $this->firstArgument;
    }

    public function getInstanceService()
    {
        return $this->instanceService;
    }

    public function getFactoryService()
    {
        return $this->factoryService;
    }

    /**
     * @return ConstructorArgumentClass
     */
    public function getSomeInstance()
    {
        return $this->someInstance;
    }
}