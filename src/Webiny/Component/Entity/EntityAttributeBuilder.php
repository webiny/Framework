<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Entity;

use Webiny\Component\Entity\Attribute\ArrayAttribute;
use Webiny\Component\Entity\Attribute\BooleanAttribute;
use Webiny\Component\Entity\Attribute\CharAttribute;
use Webiny\Component\Entity\Attribute\DateAttribute;
use Webiny\Component\Entity\Attribute\DateTimeAttribute;
use Webiny\Component\Entity\Attribute\FloatAttribute;
use Webiny\Component\Entity\Attribute\IntegerAttribute;
use Webiny\Component\Entity\Attribute\Many2ManyAttribute;
use Webiny\Component\Entity\Attribute\Many2OneAttribute;
use Webiny\Component\Entity\Attribute\One2ManyAttribute;
use Webiny\Component\Entity\Attribute\SelectAttribute;
use Webiny\Component\Entity\Attribute\TextAttribute;
use Webiny\Component\StdLib\SingletonTrait;


/**
 * EntityBuilder
 * @package Webiny\Component\Entity
 */
class EntityAttributeBuilder
{
    use SingletonTrait;

    protected $_entity;
    protected $_attributes;
    protected $_attribute;

    /**
     * Set EntityAttributeBuilder context: Entity attributes array and current attribute
     *
     * @param $attributes
     * @param $attribute
     *
     * @return $this
     */
    public function __setContext($attributes, $attribute)
    {
        $this->_attributes = $attributes;
        $this->_attribute = $attribute;

        return $this;
    }

    public function __setEntity(EntityAbstract $entity)
    {
        $this->_entity = $entity;

        return $this;
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
        $this->_attribute = $attribute;

        return $this;
    }

    /**
     * @return BooleanAttribute
     */
    public function boolean()
    {
        return $this->_attributes[$this->_attribute] = new BooleanAttribute($this->_attribute, $this->_entity);
    }

    /**
     * @return ArrayAttribute
     */
    public function arr()
    {
        return $this->_attributes[$this->_attribute] = new ArrayAttribute($this->_attribute, $this->_entity);
    }

    /**
     * @return IntegerAttribute
     */
    public function integer()
    {
        return $this->_attributes[$this->_attribute] = new IntegerAttribute($this->_attribute, $this->_entity);
    }

    /**
     * @return CharAttribute
     */
    public function char()
    {
        return $this->_attributes[$this->_attribute] = new CharAttribute($this->_attribute, $this->_entity);
    }

    /**
     * @return TextAttribute
     */
    public function text()
    {
        return $this->_attributes[$this->_attribute] = new TextAttribute($this->_attribute, $this->_entity);
    }

    /**
     * @return SelectAttribute
     */
    public function select()
    {
        return $this->_attributes[$this->_attribute] = new SelectAttribute($this->_attribute, $this->_entity);
    }

    /**
     * @return DateTimeAttribute
     */
    public function datetime()
    {
        return $this->_attributes[$this->_attribute] = new DateTimeAttribute($this->_attribute, $this->_entity);
    }

    /**
     * @return DateAttribute
     */
    public function date()
    {
        return $this->_attributes[$this->_attribute] = new DateAttribute($this->_attribute, $this->_entity);
    }

    /**
     * @return FloatAttribute
     */
    public function float()
    {
        return $this->_attributes[$this->_attribute] = new FloatAttribute($this->_attribute, $this->_entity);
    }

    /**
     * @return Many2OneAttribute
     */
    public function many2one()
    {
        return $this->_attributes[$this->_attribute] = new Many2OneAttribute($this->_attribute, $this->_entity);
    }

    /**
     * @param $relatedAttribute
     *
     * @return One2ManyAttribute
     */
    public function one2many($relatedAttribute)
    {
        return $this->_attributes[$this->_attribute] = new One2ManyAttribute($this->_attribute, $this->_entity,
                                                                             $relatedAttribute
        );
    }

    /**
     * @param string $collectionName Intermediate collection name
     *
     * @return Many2ManyAttribute
     */
    public function many2many($collectionName)
    {
        return $this->_attributes[$this->_attribute] = new Many2ManyAttribute($this->_attribute, $this->_entity,
                                                                              $collectionName
        );
    }
}