<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Entity\Attribute;

use Webiny\Component\Entity\EntityAbstract;

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
        if($value instanceof EntityAbstract){
            $value = $value->id;
        }
        if($value != null && !$this->isString($value) && !$this->isNumber($value)) {
            throw new ValidationException(ValidationException::ATTRIBUTE_VALIDATION_FAILED, [
                    $this->attribute,
                    'string, number or EntityAbstract',
                    gettype($value)
                ]
            );
        }

        // Make sure it's a string even if it is a number (convert to numeric string)
        $value = '' . $value;

        return $this;
    }
}