<?php
namespace Webiny\Component\Validation\Validators;

use Webiny\Component\Validation\ValidationException;
use Webiny\Component\Validation\ValidatorInterface;

class InArray implements ValidatorInterface
{
    public function getName()
    {
        return 'in';
    }

    public function validate($value, $params = [], $throw = false)
    {
        if (in_array($value, $params)) {
            return true;
        }

        $values = join(', ', $params);
        $message = 'Value must be one of the following: %s';
        if ($throw) {
            throw new ValidationException($message, $values);
        }

        return sprintf($message, $values);
    }
}