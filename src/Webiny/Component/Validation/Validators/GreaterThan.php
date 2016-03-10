<?php
namespace Webiny\Component\Validation\Validators;

use Webiny\Component\Validation\ValidationException;
use Webiny\Component\Validation\ValidatorInterface;

class GreaterThan implements ValidatorInterface
{
    public function getName()
    {
        return 'gt';
    }

    public function validate($value, $params = [], $throw = false)
    {
        $cmp = $params[0];

        if ($value > $cmp) {
            return true;
        }

        $message = 'Value must be greater than %s';
        if ($throw) {
            throw new ValidationException($message, $cmp);
        }

        return sprintf($message, $cmp);
    }
}