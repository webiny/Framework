<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */
namespace Webiny\Component\Entity\Validation;

use Webiny\Component\StdLib\Exception\ExceptionAbstract;

/**
 * Exception class for Entity attributes
 *
 * @package Webiny\Component\Entity\Attribute\Exception
 */
class ValidationException extends ExceptionAbstract
{
    protected $validator;

    protected static $messages = [];

    /**
     * Set validator name, eg: min, gt, unique
     *
     * @param $name
     *
     * @return $this
     */
    public function setValidator($name)
    {
        $this->validator = $name;

        return $this;
    }

    /**
     * Get validator name
     *
     * @return mixed
     */
    public function getValidator()
    {
        return $this->validator;
    }
}