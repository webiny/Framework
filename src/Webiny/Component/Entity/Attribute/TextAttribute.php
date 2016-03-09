<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Entity\Attribute;

use Webiny\Component\Entity\EntityValidationException;


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
     * @throws EntityValidationException
     * @return $this
     */
    protected function validate(&$value)
    {
        if (!$this->isString($value)) {
            $this->expected('string', gettype($value));
        }

        parent::validate($value);

        return $this;
    }
}