<?php
namespace Webiny\Component\Validation\Validators;

use Webiny\Component\Validation\ValidationException;
use Webiny\Component\Validation\ValidatorInterface;

class LessThan implements ValidatorInterface
{
    public function getName()
    {
        return 'lt';
    }

    public function validate($value, $params = [], $throw = false)
    {
        $cmp = $params[0];

        if ($value < $cmp) {
            return true;
        }

        $message = 'Value must be less than %s';
        if ($throw) {
            throw new ValidationException($message, $cmp);
        }

        return sprintf($message, $cmp);
    }
}