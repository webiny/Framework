<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Entity\Attribute;

/**
 * CharAttribute
 * @package Webiny\Component\Entity\AttributeType
 */
class CharAttribute extends AttributeAbstract
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
        if(!$this->isString($value) && !$this->isNumber($value)) {
            throw new ValidationException(ValidationException::ATTRIBUTE_VALIDATION_FAILED, [
                    $this->attribute,
                    'string or number',
                    gettype($value)
                ]
            );
        }

        // Make sure it's a string even if it is a number (convert to numeric string)
        $value = '' . $value;

        return $this;
    }
}