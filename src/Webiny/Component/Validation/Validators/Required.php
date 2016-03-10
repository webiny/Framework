<?php
namespace Webiny\Component\Validation\Validators;

use Webiny\Component\Validation\ValidationException;
use Webiny\Component\Validation\ValidatorInterface;

class Required implements ValidatorInterface
{
    public function getName()
    {
        return 'required';
    }

    public function validate($value, $params = [], $throw = false)
    {
        if (!(is_null($value) || $value === '')) {
            return true;
        }

        $message = 'Value is required';
        if ($throw) {
            throw new ValidationException($message);
        }

        return $message;
    }
}