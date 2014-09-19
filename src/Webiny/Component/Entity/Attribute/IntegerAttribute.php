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
    public function validate($value)
    {
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