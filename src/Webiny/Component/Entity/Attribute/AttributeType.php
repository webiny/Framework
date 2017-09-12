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
    const BOOLEAN = BooleanAttribute::class;
    const CHAR = CharAttribute::class;
    const DATE_TIME = DateTimeAttribute::class;
    const DATE = DateAttribute::class;
    const INTEGER = IntegerAttribute::class;
    const FLOAT = FloatAttribute::class;
    const MANY2MANY = Many2ManyAttribute::class;
    const MANY2ONE = Many2OneAttribute::class;
    const ONE2MANY = One2ManyAttribute::class;
    const ARR = ArrayAttribute::class;
    const OBJECT = ObjectAttribute::class;
    const DYNAMIC = DynamicAttribute::class;
    const GEOPOINT = GeoPointAttribute::class;
}