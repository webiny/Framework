<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Entity\Attribute;

/**
 * FloatAttribute
 * @package Webiny\Component\Entity\AttributeType
 */
class FloatAttribute extends AttributeAbstract
{
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
        if (!$this->isNumber($value)) {
            throw new ValidationException(ValidationException::ATTRIBUTE_VALIDATION_FAILED, [
                    $this->_attribute,
                    'number',
                    gettype($value)
                ]
            );
        }

        return $this;
    }
}