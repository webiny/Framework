<?php
namespace Webiny\Component\Validation\Validators;

use Webiny\Component\Validation\ValidationException;
use Webiny\Component\Validation\ValidatorInterface;

class Phone implements ValidatorInterface
{
    public function getName()
    {
        return 'phone';
    }

    public function validate($value, $params = [], $throw = false)
    {
        if (preg_match_all("/^[-+0-9()]+$/", $value)) {
            return true;
        }

        $message = 'Value must be a valid phone number';
        if ($throw) {
            throw new ValidationException($message);
        }

        return $message;
    }
}