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
     * @throws ValidationException
     * @return $this
     */
    public function validate(&$value)
    {
        $value = StdObjectWrapper::toBool($value);

        return $this;
    }

    public function getToArrayValue()
    {
        return StdObjectWrapper::toBool(parent::getToArrayValue());
    }
}