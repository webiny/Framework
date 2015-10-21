<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Entity\Attribute;

use Webiny\Component\Entity\Attribute\Exception\ValidationException;
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
    protected function validate(&$value)
    {
        if ($value instanceof EntityAbstract) {
            $value = $value->id;
        }
        if ($value != null && !$this->isString($value) && !$this->isNumber($value)) {
            $this->expected('string, number or EntityAbstract', gettype($value));
        }

        // Make sure it's a string even if it is a number (convert to numeric string)
        $value = '' . $value;

        parent::validate($value);

        return $this;
    }
}