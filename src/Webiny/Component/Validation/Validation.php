<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Validation;

use Webiny\Component\Mongo\Mongo;
use Webiny\Component\Mongo\MongoTrait;
use Webiny\Component\StdLib\ComponentTrait;
use Webiny\Component\StdLib\SingletonTrait;
use Webiny\Component\StdLib\StdLibTrait;
use Webiny\Component\Validation\Validators\Email;
use Webiny\Component\Validation\Validators\GreaterThan;


/**
 * Validation component main class
 *
 * @package \Webiny\Component\Validation
 */
class Validation
{
    use ComponentTrait, SingletonTrait;

    private $validators = [];

    /**
     * @param mixed        $data
     * @param string|array $validators
     * @param bool|true    $throw
     *
     * @return bool
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

            $res = $this->validators[$validator[0]]->validate(...$functionParams);

            // If validation failed and we are not throwing, return error message string
            if ($res !== true && !$throw) {
                return $res;
            }
        }

        return true;
    }

    protected function init()
    {
        // Load built-in validators + validation services
        $this->validators = [
            'email' => new Email(),
            'gt'    => new GreaterThan()
        ];
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