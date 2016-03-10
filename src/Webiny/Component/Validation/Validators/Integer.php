<?php
namespace Webiny\Component\Validation\Validators;

use Webiny\Component\Validation\ValidationException;
use Webiny\Component\Validation\ValidatorInterface;

class Integer implements ValidatorInterface
{
    public function getName()
    {
        return 'integer';
    }

    public function validate($value, $params = [], $throw = false)
    {
        if (is_numeric($value) && $value == intval($value)) {
            return true;
        }

        $message = 'Value must be an integer';
        if ($throw) {
            throw new ValidationException($message);
        }

        return $message;
    }
}