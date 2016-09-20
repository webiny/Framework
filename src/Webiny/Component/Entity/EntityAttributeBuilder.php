<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Entity;

use Webiny\Component\Entity\Attribute\ArrayAttribute;
use Webiny\Component\Entity\Attribute\AbstractAttribute;
use Webiny\Component\Entity\Attribute\BooleanAttribute;
use Webiny\Component\Entity\Attribute\CharAttribute;
use Webiny\Component\Entity\Attribute\DateAttribute;
use Webiny\Component\Entity\Attribute\DateTimeAttribute;
use Webiny\Component\Entity\Attribute\DynamicAttribute;
use Webiny\Component\Entity\Attribute\FloatAttribute;
use Webiny\Component\Entity\Attribute\GeoPointAttribute;
use Webiny\Component\Entity\Attribute\IntegerAttribute;
use Webiny\Component\Entity\Attribute\Many2ManyAttribute;
use Webiny\Component\Entity\Attribute\Many2OneAttribute;
use Webiny\Component\Entity\Attribute\ObjectAttribute;
use Webiny\Component\Entity\Attribute\One2ManyAttribute;
use Webiny\Component\StdLib\StdObject\ArrayObject\ArrayObject;


/**
 * EntityBuilder
 * @package Webiny\Component\Entity
 */
class EntityAttributeBuilder
{
    public static $classMap = [
        'boolean'   => '\Webiny\Component\Entity\Attribute\BooleanAttribute',
        'char'      => '\Webiny\Component\Entity\Attribute\CharAttribute',
        'integer'   => '\Webiny\Component\Entity\Attribute\IntegerAttribute',
        'float'     => '\Webiny\Component\Entity\Attribute\FloatAttribute',
        'arr'       => '\Webiny\Component\Entity\Attribute\ArrayAttribute',
        'object'    => '\Webiny\Component\Entity\Attribute\ObjectAttribute',
        'datetime'  => '\Webiny\Component\Entity\Attribute\DateTimeAttribute',
        'date'      => '\Webiny\Component\Entity\Attribute\DateAttribute',
        'many2one'  => '\Webiny\Component\Entity\Attribute\Many2OneAttribute',
        'one2many'  => '\Webiny\Component\Entity\Attribute\One2ManyAttribute',
        'many2many' => '\Webiny\Component\Entity\Attribute\Many2ManyAttribute',
        'dynamic'   => '\Webiny\Component\Entity\Attribute\DynamicAttribute',
        'geoPoint'  => '\Webiny\Component\Entity\Attribute\GeoPointAttribute'
    ];

    protected $entity;
    protected $attributes;
    protected $attribute;

    /**
     * @inheritDoc
     */
    function __construct(AbstractEntity $entity, ArrayObject $attributes)
    {
        $this->entity = $entity;
        $this->attributes = $attributes;
    }

    /**
     * Create a new attribute
     *
     * @param $attribute
     *
     * @return $this
     */
    public function attr($attribute)
    {
        $this->attribute = $attribute;

        return $this;
    }

    /**
     * Set attribute instance
     *
     * @param AbstractAttribute $attribute
     *
     * @return AbstractAttribute
     */
    public function smart(AbstractAttribute $attribute)
    {
        $attribute->setName($this->attribute)->setParent($this->entity);

        return $this->attributes[$this->attribute] = $attribute;
    }

    /**
     * @return BooleanAttribute
     */
    public function boolean()
    {
        return $this->attributes[$this->attribute] = new self::$classMap['boolean']($this->attribute, $this->entity);
    }

    /**
     * @return ArrayAttribute
     */
    public function arr()
    {
        return $this->attributes[$this->attribute] = new self::$classMap['arr']($this->attribute, $this->entity);
    }

    /**
     * @return ObjectAttribute
     */
    public function object()
    {
        return $this->attributes[$this->attribute] = new self::$classMap['object']($this->attribute, $this->entity);
    }

    /**
     * @return IntegerAttribute
     */
    public function integer()
    {
        return $this->attributes[$this->attribute] = new self::$classMap['integer']($this->attribute, $this->entity);
    }

    /**
     * @return CharAttribute
     */
    public function char()
    {
        return $this->attributes[$this->attribute] = new self::$classMap['char']($this->attribute, $this->entity);
    }

    /**
     * @return DateTimeAttribute
     */
    public function datetime()
    {
        return $this->attributes[$this->attribute] = new self::$classMap['datetime']($this->attribute, $this->entity);
    }

    /**
     * @return DateAttribute
     */
    public function date()
    {
        return $this->attributes[$this->attribute] = new self::$classMap['date']($this->attribute, $this->entity);
    }

    /**
     * @return FloatAttribute
     */
    public function float()
    {
        return $this->attributes[$this->attribute] = new self::$classMap['float']($this->attribute, $this->entity);
    }

    /**
     * @return Many2OneAttribute
     */
    public function many2one()
    {
        return $this->attributes[$this->attribute] = new self::$classMap['many2one']($this->attribute, $this->entity);
    }

    /**
     * @param $relatedAttribute
     *
     * @return One2ManyAttribute
     */
    public function one2many($relatedAttribute)
    {
        return $this->attributes[$this->attribute] = new self::$classMap['one2many']($this->attribute, $this->entity, $relatedAttribute);
    }

    /**
     * @param string $collectionName Intermediate collection name
     *
     * @return Many2ManyAttribute
     */
    public function many2many($collectionName)
    {
        return $this->attributes[$this->attribute] = new self::$classMap['many2many']($this->attribute, $this->entity, $collectionName);
    }

    /**
     * @param callable $callable
     *
     * @return DynamicAttribute
     */
    public function dynamic($callable)
    {
        return $this->attributes[$this->attribute] = new self::$classMap['dynamic']($this->attribute, $this->entity, $callable);
    }

    /**
     * @return GeoPointAttribute
     */
    public function geoPoint()
    {
        return $this->attributes[$this->attribute] = new self::$classMap['geoPoint']($this->attribute, $this->entity);
    }
}