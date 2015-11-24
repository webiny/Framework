<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Entity\Attribute;

use Webiny\Component\Entity\Validation\ValidationException;

/**
 * IntegerAttribute
 * @package Webiny\Component\Entity\AttributeType
 */
class IntegerAttribute extends AttributeAbstract
{

    public function getDbValue()
    {
        $value = $this->getValue();
        if ($this->isNull($this->value)) {
            $this->value = $value;
        }

        return $this->processToDbValue(new \MongoInt32($this->value));
    }

    /**
     * Perform validation against given value
     *
     * @param $value
     *
     * @throws ValidationException
     * @return $this
     */
    protected function validate(&$value)
    {
        if ($this->isString($value) && $this->isNumber($value)) {
            if (!$this->str($value)->contains('.') && !$this->str($value)->contains(',')) {
                $value = intval($value);
            }
        }

        if (!$this->isInteger($value)) {
            $this->expected('integer', gettype($value));
        }

        return $this;
    }

}