<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Entity\Attribute;

/**
 * ObjectAttribute
 * @package Webiny\Component\Entity\AttributeType
 */
class ObjectAttribute extends ArrayAttribute
{
    public function getDbValue()
    {
        if ($this->value->count() == 0) {
            $defaultValue = $this->getDefaultValue();
            $value = $this->isStdObject($defaultValue) ? $defaultValue->val() : $defaultValue;
        } else {
            $value = $this->value->val();
        }

        // This will force mongo to store empty object and not array
        if (count($value) == 0) {
            return new \stdClass();
        }

        return $this->processToDbValue($value);
    }
}