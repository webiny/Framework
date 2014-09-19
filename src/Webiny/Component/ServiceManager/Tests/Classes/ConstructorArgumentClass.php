<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\ServiceManager\Tests\Classes;


class ConstructorArgumentClass
{
    private $_parameter;

    public function __construct($parameter)
    {
        $this->_parameter = $parameter;
    }

    public function getConstructorParameterValue()
    {
        return $this->_parameter;
    }
}