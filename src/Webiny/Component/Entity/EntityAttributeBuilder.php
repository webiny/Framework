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

    protected $entity;
    protected $attributes;
    protected $attribute;

    /**
     * Set EntityAttributeBuilder context: Entity attributes array and current attribute
     *
     * @param $attributes
     * @param $attribute
     *
     * @return $this
     */
    public function _setContext($attributes, $attribute)
    {
        $this->attributes = $attributes;
        $this->attribute = $attribute;

        return $this;
    }

    public function _setEntity(EntityAbstract $entity)
    {
        $this->entity = $entity;

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
        $this->attribute = $attribute;

        return $this;
    }

    /**
     * @return BooleanAttribute
     */
    public function boolean()
    {
        return $this->attributes[$this->attribute] = new BooleanAttribute($this->attribute, $this->entity);
    }

    /**
     * @return ArrayAttribute
     */
    public function arr()
    {
        return $this->attributes[$this->attribute] = new ArrayAttribute($this->attribute, $this->entity);
    }

    /**
     * @return IntegerAttribute
     */
    public function integer()
    {
        return $this->attributes[$this->attribute] = new IntegerAttribute($this->attribute, $this->entity);
    }

    /**
     * @return CharAttribute
     */
    public function char()
    {
        return $this->attributes[$this->attribute] = new CharAttribute($this->attribute, $this->entity);
    }

    /**
     * @return TextAttribute
     */
    public function text()
    {
        return $this->attributes[$this->attribute] = new TextAttribute($this->attribute, $this->entity);
    }

    /**
     * @return SelectAttribute
     */
    public function select()
    {
        return $this->attributes[$this->attribute] = new SelectAttribute($this->attribute, $this->entity);
    }

    /**
     * @return DateTimeAttribute
     */
    public function datetime()
    {
        return $this->attributes[$this->attribute] = new DateTimeAttribute($this->attribute, $this->entity);
    }

    /**
     * @return DateAttribute
     */
    public function date()
    {
        return $this->attributes[$this->attribute] = new DateAttribute($this->attribute, $this->entity);
    }

    /**
     * @return FloatAttribute
     */
    public function float()
    {
        return $this->attributes[$this->attribute] = new FloatAttribute($this->attribute, $this->entity);
    }

    /**
     * @return Many2OneAttribute
     */
    public function many2one()
    {
        return $this->attributes[$this->attribute] = new Many2OneAttribute($this->attribute, $this->entity);
    }

    /**
     * @param $relatedAttribute
     *
     * @return One2ManyAttribute
     */
    public function one2many($relatedAttribute)
    {
        return $this->attributes[$this->attribute] = new One2ManyAttribute($this->attribute, $this->entity,
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
        return $this->attributes[$this->attribute] = new Many2ManyAttribute($this->attribute, $this->entity,
                                                                              $collectionName
        );
    }
}