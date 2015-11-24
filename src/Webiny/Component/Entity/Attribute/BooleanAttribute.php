<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Entity\Attribute;

use Webiny\Component\StdLib\StdObject\StdObjectWrapper;


/**
 * BooleanAttribute
 * @package Webiny\Component\Entity\AttributeType
 */
class BooleanAttribute extends AttributeAbstract
{
    /**
     * Perform validation against given value
     *
     * @param $value
     *
     * @return $this
     */
    protected function validate(&$value)
    {
        $value = StdObjectWrapper::toBool($value);

        parent::validate($value);

        return $this;
    }

    public function toArray()
    {
        return $this->processToArrayValue(StdObjectWrapper::toBool(parent::toArray()));
    }
}