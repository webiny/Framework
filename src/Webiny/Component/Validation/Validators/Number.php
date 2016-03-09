<?php
namespace Webiny\Component\Validation\Validators;

use Webiny\Component\Validation\ValidationException;
use Webiny\Component\Validation\ValidatorInterface;

class Number implements ValidatorInterface
{
    public function getName()
    {
        return 'number';
    }

    public function validate($value, $params = [], $throw = false)
    {
        if (is_numeric($value)) {
            return true;
        }

        $message = 'Value must be a number';
        if ($throw) {
            throw new ValidationException($message);
        }

        return $message;
    }
}