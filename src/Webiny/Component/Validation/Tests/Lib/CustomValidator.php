<?php
namespace Webiny\Component\Validation\Tests\Lib;

use Webiny\Component\Validation\ValidationException;
use Webiny\Component\Validation\ValidatorInterface;

class CustomValidator implements ValidatorInterface
{

    public function getName()
    {
        return 'customValidator';
    }

    public function validate($value, $params = [], $throw = true)
    {
        $message = 'Value must be an even number greater than 10';

        if ($value % 2 == 0 && $value > 10) {
            return true;
        }

        if ($throw) {
            throw new ValidationException($message);
        }

        return $message;
    }
}