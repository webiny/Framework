<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Rest;

use Webiny\Component\StdLib\Exception\ExceptionAbstract;

/**
 * Exception class for Rest component.
 *
 * @package         Webiny\Component\Rest
 */
class RestException extends ExceptionAbstract
{
    private $class = null;

    public function getRequestedClass()
    {
        return $this->class;
    }

    public function setRequestedClass($class)
    {
        $this->class = $class;

        return $this;
    }
}