<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Entity\Attribute;

use Webiny\Component\Entity\Validation\ValidationException;


/**
 * TextAttribute
 * @package Webiny\Component\Entity\AttributeType
 */
class TextAttribute extends AttributeAbstract
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
        if (!$this->isString($value)) {
            $this->expected('string', gettype($value));
        }

        return $this;
    }
}