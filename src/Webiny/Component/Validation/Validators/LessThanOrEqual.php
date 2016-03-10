<?php
namespace Webiny\Component\Validation\Validators;

use Webiny\Component\Validation\ValidationException;
use Webiny\Component\Validation\ValidatorInterface;

class LessThanOrEqual implements ValidatorInterface
{
    public function getName()
    {
        return 'lte';
    }

    public function validate($value, $params = [], $throw = false)
    {
        $cmp = $params[0];

        if ($value <= $cmp) {
            return true;
        }

        $message = 'Value must be less than or equal to %s';
        if ($throw) {
            throw new ValidationException($message, $cmp);
        }

        return sprintf($message, $cmp);
    }
}