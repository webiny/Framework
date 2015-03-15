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
    protected $message = '';

    /**
     * @var string Error description.
     */
    protected $description = '';

    /**
     * @var string Error code.
     */
    protected $code = '';

    /**
     * @var array Additional error messages. Useful if you wish to return a validation error, this can be used to
     *            store errors per-field.
     */
    protected $errors = [];


    /**
     * Base constructor.
     *
     * @param string $message     Error message.
     * @param string $description Error description.
     * @param string $code        Error code.
     */
    public function __construct($message, $description = '', $code = '')
    {
        $this->message = $message;
        $this->description = $description;
        $this->code = $code;
    }

    /**
     * Add an additional error to the exception.
     *
     * @param array $error Addition error.
     */
    public function addError(array $error)
    {
        $this->errors[] = $error;
    }

    /**
     * Get the list of all additional errors.
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Get the error message.
     *
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->message;
    }

    /**
     * Get error description.
     *
     * @return string
     */
    public function getErrorDescription()
    {
        return $this->description;
    }

    /**
     * Get error code.
     *
     * @return string
     */
    public function getErrorCode()
    {
        return $this->code;
    }
}