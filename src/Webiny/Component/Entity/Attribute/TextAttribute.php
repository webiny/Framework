<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Entity\Attribute;


/**
 * TextAttribute
 * @package Webiny\Component\Entity\AttributeType
 */

class TextAttribute extends AttributeAbstract
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
        if (!$this->isString($value)) {
            throw new ValidationException(ValidationException::ATTRIBUTE_VALIDATION_FAILED, [
                    $this->_attribute,
                    'string',
                    gettype($value)
                ]
            );
        }

        return $this;
    }
}