<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Entity\Attribute;

use MongoDB\Model\BSONDocument;

/**
 * ObjectAttribute
 * @package Webiny\Component\Entity\AttributeType
 */
class ObjectAttribute extends ArrayAttribute
{
    public function getDbValue()
    {
        $value = $this->getValue();
        if (count($value) == 0) {
            $defaultValue = $this->getDefaultValue();
            $value = $this->isStdObject($defaultValue) ? $defaultValue->val() : $defaultValue;
        }

        if ($this->isStdObject($value)) {
            $value = $value->val();
        }

        // This will force mongo to store empty object and not array
        if (count($value) == 0) {
            return new \stdClass();
        }

        return $this->processToDbValue($value);
    }

    public function setValue($value = null, $fromDb = false)
    {
        if ($fromDb && $value instanceof BSONDocument) {
            $value = $value->getArrayCopy();
        }

        return parent::setValue($value, $fromDb);
    }

    public function toArray()
    {
        if ($this->value->count() == 0) {
            $defaultValue = $this->getDefaultValue();
            $value = $this->isStdObject($defaultValue) ? $defaultValue->val() : $defaultValue;

            if (count($value) === 0) {
                $value = new \stdClass();
            }

            return $this->processToArrayValue($value);
        }

        return $this->processToArrayValue($this->value->val());
    }
}