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


    protected $_errorMessages = [];

    protected static $_messages = [
        101 => "Invalid data provided for attribute '%s'. Expecting '%s', got '%s'.",
    ];

    public function setErrorMessages($messages)
    {
        $this->_errorMessages = $messages;

        return $this;
    }

    public function getErrorMessages()
    {
        return $this->_errorMessages;
    }

}