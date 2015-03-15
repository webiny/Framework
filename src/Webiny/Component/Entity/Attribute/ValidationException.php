<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */
namespace Webiny\Component\Entity\Attribute;

use Webiny\Component\StdLib\Exception\ExceptionAbstract;

/**
 * Exception class for the ServiceManager component.
 *
 * @package         Webiny\Component\ServiceManager
 */
class ValidationException extends ExceptionAbstract
{

    const ATTRIBUTE_VALIDATION_FAILED = 101;
    const REQUIRED_ATTRIBUTE_IS_MISSING = 102;

    protected $errorMessages = [];

    protected static $messages = [
        101 => "Invalid data provided for attribute '%s'. Expecting '%s', got '%s'.",
        102 => "Missing required attribute value for attribute'%s'."
    ];

    public function setErrorMessages($messages)
    {
        $this->errorMessages = $messages;

        return $this;
    }

    public function getErrorMessages()
    {
        return $this->errorMessages;
    }

}