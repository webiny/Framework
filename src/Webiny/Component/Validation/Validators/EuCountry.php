<?php
namespace Webiny\Component\Validation\Validators;

use Webiny\Component\Validation\ValidationException;
use Webiny\Component\Validation\ValidatorInterface;

class EuCountry implements ValidatorInterface
{
    public function getName()
    {
        return 'euCountry';
    }

    public function validate($value, $params = [], $throw = false)
    {
        $message = 'Given value is not an EU country.';

        $euCountries = [
            'AT',
            'BE',
            'BG',
            'HR',
            'CY',
            'CZ',
            'DK',
            'EE',
            'FI',
            'FR',
            'DE',
            'GR',
            'HU',
            'IE',
            'IT',
            'LV',
            'LT',
            'LU',
            'MT',
            'NL',
            'PL',
            'PT',
            'RO',
            'SK',
            'SI',
            'ES',
            'SE',
            'GB',
        ];

        if (in_array($value, $euCountries)) {
            return true;
        }

        if ($throw) {
            throw new ValidationException($message);
        }

        return $message;
    }

}