<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Entity\Attribute;

/**
 * DateAttribute class supports simple dates in Y-m-d format and stores them as timestamp.
 * When retrieved from DB, formatted as Y-m-d string.
 * No timezone calculations are applied to this attribute.
 *
 * @package Webiny\Component\Entity\AttributeType
 */
class DateAttribute extends AbstractAttribute
{

    public function getDbValue()
    {
        $value = $this->getValue();
        if ($value) {
            $value = $this->datetime($this->getValue())->setTime(0, 0, 0)->getTimestamp();
        }

        return $this->processToDbValue($value);
    }

    public function setValue($value = null, $fromDb = false)
    {
        if ($fromDb && $value) {
            $value = $this->datetime($value)->format('Y-m-d');
        }

        return parent::setValue($value, $fromDb);
    }
}