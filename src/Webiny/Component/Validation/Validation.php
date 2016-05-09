<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Validation;

use Webiny\Component\ServiceManager\ServiceManagerTrait;
use Webiny\Component\StdLib\ComponentTrait;
use Webiny\Component\StdLib\SingletonTrait;
use Webiny\Component\Validation\Validators\CountryCode;
use Webiny\Component\Validation\Validators\CreditCardNumber;
use Webiny\Component\Validation\Validators\Email;
use Webiny\Component\Validation\Validators\EuCountry;
use Webiny\Component\Validation\Validators\EuVatNumber;
use Webiny\Component\Validation\Validators\GeoLocation;
use Webiny\Component\Validation\Validators\GreaterThan;
use Webiny\Component\Validation\Validators\GreaterThanOrEqual;
use Webiny\Component\Validation\Validators\InArray;
use Webiny\Component\Validation\Validators\Integer;
use Webiny\Component\Validation\Validators\LessThan;
use Webiny\Component\Validation\Validators\LessThanOrEqual;
use Webiny\Component\Validation\Validators\MaxLength;
use Webiny\Component\Validation\Validators\MinLength;
use Webiny\Component\Validation\Validators\Number;
use Webiny\Component\Validation\Validators\Password;
use Webiny\Component\Validation\Validators\Phone;
use Webiny\Component\Validation\Validators\Regex;
use Webiny\Component\Validation\Validators\Required;
use Webiny\Component\Validation\Validators\Url;


/**
 * Validation component main class
 *
 * @package \Webiny\Component\Validation
 */
class Validation
{
    use ComponentTrait, SingletonTrait, ServiceManagerTrait;

    private $validators = [];

    /**
     * @param mixed        $data
     * @param string|array $validators
     * @param bool|true    $throw
     *
     * @return bool
     * @throws ValidationException
     */
    public function validate($data, $validators, $throw = true)
    {
        $validators = $this->getValidators($validators);
        foreach ($validators as $validator) {
            $validator = explode(':', $validator);

            $functionParams = [
                $data,
                array_splice($validator, 1),
                $throw
            ];

            if (!array_key_exists($validator[0], $this->validators)) {
                throw new ValidationException('Validator %s does not exist!', $validator[0]);
            }
            $res = $this->validators[$validator[0]]->validate(...$functionParams);

            // If validation failed and we are not throwing, return error message string
            if ($res !== true && !$throw) {
                return $res;
            }
        }

        return true;
    }

    /**
     * Add validator to Validation component
     *
     * @param ValidatorInterface $validator
     *
     * @return $this
     */
    public function addValidator(ValidatorInterface $validator)
    {
        $this->validators[$validator->getName()] = $validator;

        return $this;
    }

    protected function init()
    {
        // Load built-in validators
        $builtInValidators = [
            new Email(),
            new GreaterThan(),
            new GreaterThanOrEqual(),
            new GeoLocation(),
            new LessThan(),
            new LessThanOrEqual(),
            new MinLength(),
            new MaxLength(),
            new InArray(),
            new Number(),
            new Integer(),
            new Url(),
            new Password(),
            new Required(),
            new Phone(),
            new CountryCode(),
            new CreditCardNumber(),
            new EuVatNumber(),
            new EuCountry(),
            new Regex()
        ];

        /* @var $v ValidatorInterface */
        foreach ($builtInValidators as $v) {
            $this->validators[$v->getName()] = $v;
        }

        // Load validators registered as a service
        $validators = $this->servicesByTag('validation-plugin', '\Webiny\Component\Validation\ValidatorInterface');
        foreach ($validators as $v) {
            $this->validators[$v->getName()] = $v;
        }
    }

    private function getValidators($validators)
    {
        if (is_array($validators)) {
            return $validators;
        }

        if (is_string($validators)) {
            return explode(',', $validators);
        }

        return [];
    }
}