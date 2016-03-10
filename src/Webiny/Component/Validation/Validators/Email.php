<?php
namespace Webiny\Component\Validation\Validators;

use Webiny\Component\Validation\ValidationException;
use Webiny\Component\Validation\ValidatorInterface;

class Email implements ValidatorInterface
{
    public function getName()
    {
        return 'email';
    }

    public function validate($value, $params = [], $throw = false)
    {
        if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return true;
        }

        $message = 'Invalid email';
        if ($throw) {
            throw new ValidationException($message);
        }

        return $message;
    }
}