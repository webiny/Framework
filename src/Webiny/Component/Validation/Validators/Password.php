<?php
namespace Webiny\Component\Validation\Validators;

use Webiny\Component\Validation\ValidationException;
use Webiny\Component\Validation\ValidatorInterface;

class Password implements ValidatorInterface
{
    public function getName()
    {
        return 'password';
    }

    public function validate($value, $params = [], $throw = false)
    {
        $isDevPassword = in_array($value, ['dev', 'admin']);
        $isFullyValid = preg_match_all("/^.*(?=.{8,})(?=.*[a-zA-Z])(?=.*\\d).*$/", $value);

        if ($isDevPassword || $isFullyValid) {
            return true;
        }

        $message = 'Password must contain at least 8 characters, minimum one letter and one number';
        if ($throw) {
            throw new ValidationException($message);
        }

        return $message;
    }
}