<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Entity\Attribute;

use Webiny\Component\Entity\Attribute\Validation\ValidationException;

/**
 * FloatAttribute
 * @package Webiny\Component\Entity\AttributeType
 */
class FloatAttribute extends AbstractAttribute
{

    public function getDbValue()
    {
        $value = floatval($this->getValue());
        if ($this->isNull($this->value)) {
            $this->value = floatval($value);
        }

        return $this->processToDbValue($value);
    }

    /**
     * Get value that will be used to represent this attribute when converting AbstractEntity to array
     *
     * @param array $params
     *
     * @return string
     */
    public function toArray($params = [])
    {
        return (float)parent::toArray($params);
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
        if ($this->str($value)->contains(',')) {
            $value = $this->str($value)->replace(',', '.')->val();
        }
        $value = floatval($value);

        if (!$this->isNumber($value)) {
            $this->expected('number', gettype($value));
        }

        parent::validate($value);

        return $this;
    }
}