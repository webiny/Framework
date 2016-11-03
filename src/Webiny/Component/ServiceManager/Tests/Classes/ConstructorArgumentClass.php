<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\ServiceManager\Tests\Classes;


class ConstructorArgumentClass
{
    private $parameter;

    public function __construct($parameter, InstanceService $service)
    {
        $this->parameter = $parameter;
    }

    public function getConstructorParameterValue()
    {
        return $this->parameter;
    }
}