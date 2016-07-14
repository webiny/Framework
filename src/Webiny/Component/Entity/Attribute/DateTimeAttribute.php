<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Entity\Attribute;

/**
 * DateTimeAttribute class supports dates with time in Y-m-d H:i:s format
 *
 * @package Webiny\Component\Entity\AttributeType
 */
class DateTimeAttribute extends AbstractDateAttribute
{

    /**
     * Get unix timestamp from current attribute value<br>
     * Returns null if value is not set
     *
     * @return int|null
     */
    public function getTimestamp()
    {
        if ($this->isNull($this->value)) {
            return null;
        }
        $value = strtotime($this->getValue());

        return $value ?: null;
    }
}