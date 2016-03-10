<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Entity\Attribute;

use Webiny\Component\Entity\EntityAbstract;

/**
 * SmartAttributeAbstract
 * @package Webiny\Component\Entity\AttributeType
 */
abstract class SmartAttributeAbstract extends AttributeAbstract
{
    public function __construct($attribute, EntityAbstract $entity)
    {
        // Override parent constructor
    }

    public function setAttribute($attribute)
    {
        $this->attribute = $attribute;

        return $this;
    }

    public function setParent(EntityAbstract $entity)
    {
        $this->entity = $entity;

        return $this;
    }

}