<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Entity\Attribute;

/**
 * IntegerAttribute
 * @package Webiny\Component\Entity\AttributeType
 */
class IntegerAttribute extends AttributeAbstract
{

    public function getDbValue()
    {
        $value = $this->getValue();
        if($this->isNull($this->_value)){
            $this->_value = $value;
        }
        return new \MongoInt32($this->_value);
    }

    /**
     * Perform validation against given value
     *
     * @param $value
     *
     * @throws ValidationException
     * @return $this
     */
    public function validate(&$value)
    {
        if($this->isString($value) && $this->isNumber($value)){
            if(!$this->str($value)->contains('.') && !$this->str($value)->contains(',')){
                $value = intval($value);
            }
        }

        if (!$this->isInteger($value)) {
            throw new ValidationException(ValidationException::ATTRIBUTE_VALIDATION_FAILED, [
                    $this->_attribute,
                    'integer',
                    gettype($value)
                ]
            );
        }

        return $this;
    }

}