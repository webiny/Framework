<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Entity\Attribute;

/**
 * DateAttribute class supports simple dates in Y-m-d format
 *
 * @package Webiny\Component\Entity\AttributeType
 */
class DateAttribute extends DateAttributeAbstract
{

    protected $attributeFormat = 'Y-m-d';

}