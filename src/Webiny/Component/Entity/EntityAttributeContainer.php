<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Entity;

use ArrayIterator;
use Traversable;
use Webiny\Component\Entity\Attribute\ArrayAttribute;
use Webiny\Component\Entity\Attribute\AbstractAttribute;
use Webiny\Component\Entity\Attribute\AttributeType;
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

/**
 * EntityAttributeContainer
 * @package Webiny\Component\Entity
 */
class EntityAttributeContainer implements \ArrayAccess, \IteratorAggregate
{
    public static $classMap = [
        'boolean'   => AttributeType::BOOLEAN,
        'char'      => AttributeType::CHAR,
        'integer'   => AttributeType::INTEGER,
        'float'     => AttributeType::FLOAT,
        'arr'       => AttributeType::ARR,
        'object'    => AttributeType::OBJECT,
        'datetime'  => AttributeType::DATE_TIME,
        'date'      => AttributeType::DATE,
        'many2one'  => AttributeType::MANY2ONE,
        'one2many'  => AttributeType::ONE2MANY,
        'many2many' => AttributeType::MANY2MANY,
        'dynamic'   => AttributeType::DYNAMIC,
        'geoPoint'  => AttributeType::GEOPOINT
    ];

    protected $entity;
    protected $attributes = [];
    protected $attribute;

    /**
     * @inheritDoc
     */
    function __construct(AbstractEntity $entity)
    {
        $this->entity = $entity;
    }

    /**
     * Create a new attribute
     *
     * @param $attribute
     *
     * @return $this
     * @throws EntityException
     */
    public function attr($attribute)
    {
        if (strpos($attribute, '_') !== false) {
            throw new EntityException('Underscore is not allowed in attribute names (found in \'' . $attribute . '\')');
        }

        $this->attribute = $attribute;

        return $this;
    }

    /**
     * Remove one or more attributes
     * Ex: remove('name', 'name2', 'name3');
     *
     * @param array|string ...$attributes
     */
    public function remove(...$attributes)
    {
        foreach ($attributes as $attr) {
            unset($this->attributes[$attr]);
        }
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
     * @param string $thisField Field name containing ID of this entity
     * @param string $refField Field name containing ID of referenced entity
     *
     * @return Many2ManyAttribute
     */
    public function many2many($collectionName, $thisField, $refField)
    {
        $params = [$this->attribute, $thisField, $refField, $this->entity, $collectionName];

        return $this->attributes[$this->attribute] = new self::$classMap['many2many'](...$params);
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

    /**
     * @inheritdoc
     */
    public function getIterator()
    {
        return new ArrayIterator($this->attributes);
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->attributes);
    }

    /**
     * @inheritdoc
     */
    public function offsetGet($offset)
    {
        return $this->attributes[$offset];
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value)
    {
        $this->attributes[$offset] = $value;
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset)
    {
        unset($this->attributes[$offset]);
    }
}