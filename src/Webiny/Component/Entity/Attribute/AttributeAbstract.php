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
        if ($this->isNull($this->_value)) {
            return '';
        }

        return (string)$this->_value;
    }

    /**
     * Get value that will be stored to database
     *
     * @return string
     */
    public function getDbValue()
    {
        return $this->getValue();
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
        if ($this->isNull($attribute)) {
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
        $this->validate($value);
        if ($this->_value != $value) {
            $this->_entity->__dirty(true);
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
        if ($this->isNull($this->_value) && $this->_defaultValue) {
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
}