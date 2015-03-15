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
    private $injectedService;

    /**
     * @var ConstructorArgumentClass
     */
    private $someInstance;

    public function __construct($simpleArgument, $secondService, ConstructorArgumentClass $someInstance)
    {
        $this->firstArgument = $simpleArgument;
        $this->injectedService = $secondService;
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

    public function getInjectedServiceValue()
    {
        return $this->injectedService;
    }

    /**
     * @return ConstructorArgumentClass
     */
    public function getSomeInstance()
    {
        return $this->someInstance;
    }
}