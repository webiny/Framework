<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Entity\Attribute;

use Traversable;
use Webiny\Component\Entity\EntityAbstract;
use Webiny\Component\Entity\Validation\ValidationException;
use Webiny\Component\Entity\Attribute\Exception\ValidationException as AttributeValidationException;
use Webiny\Component\StdLib\StdObject\ArrayObject\ArrayObject;

/**
 * ObjectAttribute
 * @package Webiny\Component\Entity\AttributeType
 */
class ObjectAttribute extends ArrayAttribute
{
    public function getDbValue()
    {
        if ($this->value->count() == 0) {
            $value = $this->isStdObject($this->defaultValue) ? $this->defaultValue->val() : $this->defaultValue;
        } else {
            $value = $this->value->val();
        }

        // This will force mongo to store empty object and not array
        if(count($value) == 0){
            return new \stdClass();
        }

        return $this->processToDbValue($value);
    }
}