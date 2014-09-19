<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Rest;

use Webiny\Component\StdLib\Exception\ExceptionAbstract;

/**
 * RestErrorExceptions are thrown by the api service methods.
 * Implemented methods can throw this exception to notify the rest component that there has been an error.
 *
 * @package         Webiny\Component\Rest
 */
class RestErrorException extends ExceptionAbstract
{
    /**
     * @var string Error message.
     */
    private $_message = '';

    /**
     * @var string Error description.
     */
    private $_description = '';

    /**
     * @var string Error code.
     */
    private $_code = '';

    /**
     * @var array Additional error messages. Useful if you wish to return a validation error, this can be used to
     *            store errors per-field.
     */
    private $_errors = [];


    /**
     * Base constructor.
     *
     * @param string $message     Error message.
     * @param string $description Error description.
     * @param string $code        Error code.
     */
    public function __construct($message, $description = '', $code = '')
    {
        $this->_message = $message;
        $this->_description = $description;
        $this->_code = $code;
    }

    /**
     * Add an additional error to the exception.
     *
     * @param array $error Addition error.
     */
    public function addError(array $error)
    {
        $this->_errors[] = $error;
    }

    /**
     * Get the list of all additional errors.
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->_errors;
    }

    /**
     * Get the error message.
     *
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->_message;
    }

    /**
     * Get error description.
     *
     * @return string
     */
    public function getErrorDescription()
    {
        return $this->_description;
    }

    /**
     * Get error code.
     *
     * @return string
     */
    public function getErrorCode()
    {
        return $this->_code;
    }
}