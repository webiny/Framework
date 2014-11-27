<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Entity\Attribute;


/**
 * BooleanAttribute
 * @package Webiny\Component\Entity\AttributeType
 */
class BooleanAttribute extends AttributeAbstract
{
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
        if (!$this->isBool($value)) {
            throw new ValidationException(ValidationException::ATTRIBUTE_VALIDATION_FAILED, [
                    $this->_attribute,
                    'boolean',
                    gettype($value)
                ]
            );
        }

        return $this;
    }
}