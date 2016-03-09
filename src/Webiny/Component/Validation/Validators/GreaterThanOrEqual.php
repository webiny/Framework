<?php
namespace Webiny\Component\Validation\Validators;

use Webiny\Component\Validation\ValidationException;
use Webiny\Component\Validation\ValidatorInterface;

class GreaterThanOrEqual implements ValidatorInterface
{
    public function getName()
    {
        return 'gte';
    }

    public function validate($value, $params = [], $throw = false)
    {
        $cmp = $params[0];

        if ($value >= $cmp) {
            return true;
        }

        $message = 'Value must be greater than or equal to %s';
        if ($throw) {
            throw new ValidationException($message, $cmp);
        }

        return sprintf($message, $cmp);
    }
}