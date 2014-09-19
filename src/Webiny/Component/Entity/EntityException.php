<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */
namespace Webiny\Component\Entity;

use Webiny\Component\StdLib\Exception\ExceptionAbstract;

/**
 * Exception class for the Entity component.
 *
 * @package         Webiny\Component\Entity
 */
class EntityException extends ExceptionAbstract
{

    const VALIDATION_FAILED = 101;
    const ENTITY_DELETION_RESTRICTED = 102;
    const NO_MATCHING_MANY2MANY_ATTRIBUTE_FOUND = 103;
    const ATTRIBUTE_NOT_FOUND = 104;
    const INVALID_MANY2MANY_VALUE = 105;
    const INVALID_ONE2MANY_VALUE = 106;

    protected $_invalidAttributes = [];

    protected static $_messages = [
        101 => "Entity validation failed with '%s' errors.",
        102 => "Entity is linked with other entities via '%s' attribute and can not be deleted.",
        103 => "No matching Many2Many attribute was found between entities '%s' and '%s' for attribute '%s'.",
        104 => "AttributeType '%s' was not found in '%s' entity.",
        105 => "Many2Many attribute '%s' expects '%s', instance of '%s' given.",
        106 => "One2Many attribute '%s' expects an instance of '%s', instance of '%s' given.",
    ];

    public function setInvalidAttributes($attributes)
    {
        $this->_invalidAttributes = $attributes;

        return $this;
    }

    public function getInvalidAttributes()
    {
        return $this->_invalidAttributes;
    }

}