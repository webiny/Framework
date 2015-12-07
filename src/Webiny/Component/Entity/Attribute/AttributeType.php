<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Entity\Attribute;


/**
 * AttributeType class
 * @package Webiny\Component\Entity\AttributeType
 *
 */
class AttributeType
{
    const BOOLEAN = '\Webiny\Component\Entity\Attribute\BooleanAttribute';
    const CHAR = '\Webiny\Component\Entity\Attribute\CharAttribute';
    const DATE_TIME = '\Webiny\Component\Entity\Attribute\DateTimeAttribute';
    const DATE = '\Webiny\Component\Entity\Attribute\DateAttribute';
    const INTEGER = '\Webiny\Component\Entity\Attribute\IntegerAttribute';
    const FLOAT = '\Webiny\Component\Entity\Attribute\FloatAttribute';
    const MANY2MANY = '\Webiny\Component\Entity\Attribute\Many2ManyAttribute';
    const MANY2ONE = '\Webiny\Component\Entity\Attribute\Many2OneAttribute';
    const ONE2MANY = '\Webiny\Component\Entity\Attribute\One2ManyAttribute';
    const SELECT = '\Webiny\Component\Entity\Attribute\SelectAttribute';
    const TEXT = '\Webiny\Component\Entity\Attribute\TextAttribute';
    const ARR = '\Webiny\Component\Entity\Attribute\ArrayAttribute';
    const OBJECT = '\Webiny\Component\Entity\Attribute\ObjectAttribute';
    const DYNAMIC = '\Webiny\Component\Entity\Attribute\DynamicAttribute';
}