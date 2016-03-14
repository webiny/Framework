<?php
namespace Webiny\Component\Validation\Validators;

use Webiny\Component\Validation\ValidationException;
use Webiny\Component\Validation\ValidatorInterface;

class Regex implements ValidatorInterface
{
    public function getName()
    {
        return 'regex';
    }

    public function validate($value, $params = [], $throw = false)
    {
        if (preg_match_all($params[0], $value)) {
            return true;
        }

        $message = 'Value must match the pattern ' . $params[0];
        if ($throw) {
            throw new ValidationException($message);
        }

        return $message;
    }
}