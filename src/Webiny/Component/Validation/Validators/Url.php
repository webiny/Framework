<?php
namespace Webiny\Component\Validation\Validators;

use Webiny\Component\Validation\ValidationException;
use Webiny\Component\Validation\ValidatorInterface;

class Url implements ValidatorInterface
{
    public function getName()
    {
        return 'url';
    }

    public function validate($value, $params = [], $throw = false)
    {
        if (filter_var($value, FILTER_VALIDATE_URL, FILTER_FLAG_HOST_REQUIRED)) {
            return true;
        }

        $message = 'Value must be a valid URL';
        if ($throw) {
            throw new ValidationException($message);
        }

        return $message;
    }
}