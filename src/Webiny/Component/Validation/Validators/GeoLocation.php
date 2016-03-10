<?php
namespace Webiny\Component\Validation\Validators;

use Webiny\Component\Validation\ValidationException;
use Webiny\Component\Validation\ValidatorInterface;

class GeoLocation implements ValidatorInterface
{
    public function getName()
    {
        return 'geoLocation';
    }

    public function validate($value, $params = [], $throw = false)
    {
        if (is_array($value) && isset($value['lat']) && isset($value['lng'])) {
            return true;
        }

        $message = 'Value must be an array containing keys `lat` and `lng`';
        if ($throw) {
            throw new ValidationException($message);
        }

        return $message;
    }
}