<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Entity\Attribute;

use MongoDB\BSON\UTCDatetime;
use Webiny\Component\Entity\Attribute\Validation\ValidationException;
use Webiny\Component\StdLib\StdObject\DateTimeObject\DateTimeObject;
use Webiny\Component\StdLib\StdObject\DateTimeObject\DateTimeObjectException;

/**
 * AbstractDateAttribute
 * @package Webiny\Component\Entity\AttributeType
 */
abstract class AbstractDateAttribute extends AbstractAttribute
{
    protected $autoUpdate = false;

    public function getDbValue()
    {
        $value = null;

        if ($this->isEmpty($this->value)) {
            $this->setDefaultValueInternal();
        }

        if ($this->autoUpdate && $this->parent->exists()) {
            $this->setValue(new DateTimeObject());
        }

        if ($this->getValue()) {
            $value = new UTCDatetime(strtotime($this->getValue()) * 1000);
        }

        return $this->processToDbValue($value);
    }

    /**
     * Set auto update on or off<br>
     * If true, will update the attribute value with current date/datetime each time it's inserted into DB
     *
     * @param bool $flag
     *
     * @return $this
     */
    public function setAutoUpdate($flag = true)
    {
        $this->autoUpdate = $flag;

        return $this;
    }

    public function setValue($value = null, $fromDb = false)
    {
        if ($value === null) {
            return parent::setValue($value, $fromDb);
        }

        if ($value instanceof UTCDatetime) {
            $value = $value->toDateTime()->format(DATE_ISO8601);
        } elseif ($value instanceof DateTimeObject) {
            $value = $value->format(DATE_ISO8601);
        } elseif ($value == 'now') {
            $value = $this->datetime()->format(DATE_ISO8601);
        } else {
            $value = $this->datetime($value)->setTimezone('UTC')->format(DATE_ISO8601);
        }

        return parent::setValue($value, $fromDb);
    }

    public function getValue($params = [], $asDateTimeObject = false)
    {
        if ($asDateTimeObject) {
            return $this->processGetValue(new DateTimeObject(parent::getValue()), $params);
        }

        return $this->processGetValue(parent::getValue(), $params);
    }

    /**
     * Perform validation against given value
     *
     * @param $value
     *
     * @throws ValidationException
     * @return $this
     */
    protected function validate(&$value)
    {
        if ($value instanceof DateTimeObject) {
            $value = $value->format(DATE_ISO8601);
        }

        if ($value instanceof UTCDatetime) {
            if ($value->sec == 0) {
                return $this;
            }
            $value = $value->toDateTime()->format(DATE_ISO8601);
        }
        try {
            new DateTimeObject($value);
        } catch (DateTimeObjectException $e) {
            $this->expected('Unix timestamp, string or DateTimeObject', gettype($value));
        }

        return $this;
    }

    /**
     * Set default attribute value
     */
    private function setDefaultValueInternal()
    {
        $defaultValue = $this->getDefaultValue();
        if ($defaultValue == 'now') {
            $defaultValue = new DateTimeObject('now');
        }
        $this->setValue($defaultValue);
    }
}