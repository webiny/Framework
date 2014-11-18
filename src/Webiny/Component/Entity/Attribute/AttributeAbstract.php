<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Entity\Attribute;

use Webiny\Component\Entity\EntityAbstract;
use Webiny\Component\Entity\EntityAttributeBuilder;
use Webiny\Component\StdLib\StdLibTrait;


/**
 * AttributeAbstract
 * @package Webiny\Component\Entity\AttributeType
 *
 */
abstract class AttributeAbstract
{
    use StdLibTrait;

    /**
     * @var EntityAbstract
     */

    protected $_entity;
    protected $_attribute = '';
    protected $_defaultValue = null;
    protected $_value = null;
    protected $_required = false;
    protected $_once = false;

    /**
     * @param string         $attribute
     * @param EntityAbstract $entity
     */
    public function __construct($attribute, EntityAbstract $entity)
    {
        $this->_attribute = $attribute;
        $this->_entity = $entity;
    }

    /**
     * Get attribute value as string
     *
     * @return string
     */
    public function __toString()
    {
        if($this->isNull($this->_value) && !$this->isNull($this->_defaultValue)) {
            return (string)$this->_defaultValue;
        }

        return $this->isNull($this->_value) ? '' : (string)$this->_value;
    }

    /**
     * Get value that will be stored to database<br>
     *
     * If no value is set, default value will be used, and that value will also be assigned as a new attribute value.
     *
     * @return string
     */
    public function getDbValue()
    {
        $value = $this->getValue();
        if($this->isNull($this->_value)) {
            $this->_value = $value;
        }

        return $value;
    }

    /**
     * Get value that will be used when converting EntityAbstract to array
     * @return string
     */
    public function getToArrayValue()
    {
        return (string)$this;
    }

    /**
     * Create new attribute or get name of current attribute
     *
     * @param null|string $attribute
     *
     * @return EntityAttributeBuilder
     */
    public function attr($attribute = null)
    {
        if($this->isNull($attribute)) {
            return $this->_attribute;
        }

        return EntityAttributeBuilder::getInstance()->attr($attribute);
    }

    /**
     * Set required flag
     *
     * @param boolean $flag
     *
     * @return $this
     */
    public function setRequired($flag = true)
    {
        $this->_required = $flag;

        return $this;
    }

    /**
     * Get required flag
     *
     * @return $this
     */
    public function getRequired()
    {
        return $this->_required;
    }

    /**
     * Set 'once' flag<br>
     * If true, it tells EntityAbstract to only populate this attribute if it's a new entity<br>
     * This is useful when you want to protect values from being populate on secondary updates.
     *
     * @param boolean $flag
     *
     * @return $this
     */
    public function setOnce($flag = true)
    {
        $this->_once = $flag;

        return $this;
    }

    /**
     * Get 'once' flag<br>
     * This flag tells you whether this attribute should only be populated when it's a new EntityAbstract instance.<br>
     * By default, attributes are populated each time.
     *
     * @return $this
     */
    public function getOnce()
    {
        return $this->_once;
    }


    /**
     * Set default value
     *
     * @param mixed $defaultValue
     *
     * @return $this
     */
    public function setDefaultValue($defaultValue = null)
    {
        $this->_defaultValue = $defaultValue;

        return $this;
    }

    /**
     * Get default value
     *
     * @return mixed
     */
    public function getDefaultValue()
    {
        return $this->_defaultValue;
    }

    /**
     * Set attribute value
     *
     * @param null $value
     *
     * @return $this
     */
    public function setValue($value = null)
    {
        if(!$this->_canAssign()) {
            return $this;
        }

        $this->validate($value);
        if($this->_value != $value) {
            $this->_entity->__setDirty(true);
        }
        $this->_value = $value;

        return $this;
    }

    /**
     * Get attribute value
     *
     * @return $this
     */
    public function getValue()
    {
        if($this->isNull($this->_value) && !$this->isNull($this->_defaultValue)) {
            return $this->_defaultValue;
        }

        return $this->_value;
    }

    /**
     * Perform validation against given value
     *
     * @param $value
     *
     * @throws ValidationException
     * @return $this
     */
    public function validate($value)
    {
        return $this;
    }

    /**
     * Check if value can be assigned to current attribute<br>
     *
     * If Entity has an ID and attribute 'once' flag is set and attribute has a value assigned to it
     * then a new value should not be assigned.
     *
     * @return bool
     */
    protected function _canAssign()
    {
        if($this->_entity->getId()->getValue() && $this->getOnce() && $this->_value !== null) {
            return false;
        }

        return true;
    }
}