<?php
namespace Webiny\Component\Validation\Validators;

use Webiny\Component\Validation\ValidationException;
use Webiny\Component\Validation\ValidatorInterface;

class MinLength implements ValidatorInterface
{
    public function getName()
    {
        return 'minLength';
    }

    public function validate($value, $params = [], $throw = false)
    {
        $limit = $params[0];
        $length = is_string($value) ? strlen($value) : count($value);
        if ($length >= $limit) {
            return true;
        }

        if (is_numeric($value) && $value < $limit) {
            return true;
        }

        $message = 'Value must contain %s characters at least';
        if ($throw) {
            throw new ValidationException($message, $limit);
        }

        return sprintf($message, $limit);
    }
}