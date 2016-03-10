<?php
namespace Webiny\Component\Validation\Validators;

use Webiny\Component\Validation\ValidationException;
use Webiny\Component\Validation\ValidatorInterface;

class CreditCardNumber implements ValidatorInterface
{
    public function getName()
    {
        return 'creditCardNumber';
    }

    public function validate($value, $params = [], $throw = false)
    {
        $cardArray = [
            'default'          => [
                'length' => '13,14,15,16,17,18,19',
                'prefix' => '',
                'luhn'   => true,
            ],
            'american express' => [
                'length' => '15',
                'prefix' => '3[47]',
                'luhn'   => true,
            ],
            'diners club'      => [
                'length' => '14,16',
                'prefix' => '36|55|30[0-5]',
                'luhn'   => true,
            ],
            'discover'         => [
                'length' => '16',
                'prefix' => '6(?:5|011)',
                'luhn'   => true,
            ],
            'jcb'              => [
                'length' => '15,16',
                'prefix' => '3|1800|2131',
                'luhn'   => true,
            ],
            'maestro'          => [
                'length' => '16,18',
                'prefix' => '50(?:20|38)|6(?:304|759)',
                'luhn'   => true,
            ],
            'mastercard'       => [
                'length' => '16',
                'prefix' => '5[1-5]',
                'luhn'   => true,
            ],
            'visa'             => [
                'length' => '13,16',
                'prefix' => '4',
                'luhn'   => true,
            ]
        ];

        $message = 'Value must be a valid credit card number';

        // Remove all non-digit characters from the number
        if (($number = preg_replace('/\D+/', '', $value)) === '') {
            if ($throw) {
                throw new ValidationException($message);
            }

            return $message;
        }

        // Use the default type
        $type = 'default';

        $cards = $cardArray;

        // Check card type
        $type = strtolower($type);

        if (!isset($cards[$type])) {
            if ($throw) {
                throw new ValidationException($message);
            }

            return $message;
        }

        // Check card number length
        $length = strlen($number);

        // Validate the card length by the card type
        if (!in_array($length, preg_split('/\D+/', $cards[$type]['length']))) {
            if ($throw) {
                throw new ValidationException($message);
            }

            return $message;
        }

        // Check card number prefix
        if (!preg_match('/^' . $cards[$type]['prefix'] . '/', $number)) {
            if ($throw) {
                throw new ValidationException($message);
            }

            return $message;
        }

        // No Luhn check required
        if ($cards[$type]['luhn'] == false) {
            return true;
        }

        if ($this->luhn($number)) {
            return true;
        }

        if ($throw) {
            throw new ValidationException($message);
        }

        return $message;
    }

    private function luhn($number)
    {
        // Force the value to be a string as this method uses string functions.
        // Converting to an integer may pass PHP_INT_MAX and result in an error!
        $number = (string)$number;

        if (!ctype_digit($number)) {
            // Luhn can only be used on numbers!
            return false;
        }

        // Check number length
        $length = strlen($number);

        // Checksum of the card number
        $checksum = 0;

        for ($i = $length - 1; $i >= 0; $i -= 2) {
            // Add up every 2nd digit, starting from the right
            $checksum += intval(substr($number, $i, 1));
        }

        for ($i = $length - 2; $i >= 0; $i -= 2) {
            // Add up every 2nd digit doubled, starting from the right
            $double = substr($number, $i, 1) * 2;

            // Subtract 9 from the double where value is greater than 10
            $checksum += intval(($double >= 10) ? ($double - 9) : $double);
        }

        // If the checksum is a multiple of 10, the number is valid
        return ($checksum % 10 === 0);
    }
}