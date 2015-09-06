<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Entity\Attribute;

use Webiny\Component\Entity\Entity;
use Webiny\Component\StdLib\StdLibTrait;


/**
 * Many2One attribute
 * @package Webiny\Component\Entity\AttributeType
 */
class Many2OneAttribute extends AttributeAbstract
{
    use StdLibTrait;

    protected $entityClass = null;

    /**
     * @var null|\Closure
     */
    protected $setNull = null;

    /**
     * Get masked entity value when instance is being converted to string
     *
     * @return mixed|null|string
     */
    public function __toString()
    {
        if ($this->isNull($this->value) && !$this->isNull($this->defaultValue)) {
            return (string)$this->defaultValue;
        }

        if ($this->isNull($this->value)) {
            return '';
        }

        return $this->getValue()->getMaskedValue();
    }

    /**
     * Get related entity ID
     * @return CharAttribute
     */
    public function getId()
    {
        $value = $this->getValue();
        if ($value) {
            return $value->getId();
        }

        return null;
    }

    /**
     * Get value that will be stored to database
     *
     * @return string
     */
    public function getDbValue()
    {
        $value = $this->getValue();
        if ($this->isNull($value)) {
            return null;
        }

        // If what we got is a defaultValue - create or load an actual entity instance
        if ($value === $this->defaultValue) {
            if ($this->isArray($value) || $this->isArrayObject($value)) {
                $this->value = new $this->entityClass;
                $this->value->populate($value);
            }

            if (Entity::getInstance()->getDatabase()->isMongoId($value)) {
                $this->value = call_user_func_array([
                    $this->entityClass,
                    'findById'
                ], [$value]);
            }
        }

        if ($this->getValue()->id === null) {
            $this->getValue()->save();
        }

        // Return a simple Entity ID string
        return $this->getValue()->id;
    }

    /**
     * Set entity class for this attribute
     *
     * @param string $entityClass
     *
     * @return $this
     */
    public function setEntity($entityClass)
    {
        $this->entityClass = $entityClass;

        return $this;
    }

    /**
     * Get entity class for this attribute
     *
     * @return string
     */
    public function getEntity()
    {
        return $this->entityClass;
    }

    /**
     * Get attribute value
     *
     * @return bool|null|\Webiny\Component\Entity\EntityAbstract
     */
    public function getValue()
    {
        if (!$this->isInstanceOf($this->value, $this->entityClass) && !empty($this->value)) {
            $data = null;
            if ($this->isArray($this->value) || $this->isArrayObject($this->value)) {
                $data = $this->value;
                $this->value = isset($data['id']) ? $data['id'] : false;
            }

            $this->value = call_user_func_array([
                $this->entityClass,
                'findById'
            ], [$this->value]);

            if($this->value){
                $this->value->populate($data);
            } else {
                $this->value = new $this->entityClass;
                $this->value->populate($data);
            }
        }

        if (!$this->value && !$this->isNull($this->defaultValue)) {
            return $this->defaultValue;
        }

        return $this->value;
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
        if (!$this->canAssign()) {
            return $this;
        }

        $this->validate($value);

        // Execute setNull callback
        if($this->setNull && is_null($value) && $this->value){
            $callable = $this->setNull;
            if($callable == 'delete'){
                $this->getValue()->delete();
            } else {
                $callable($this->getValue());
            }
        }

        $this->value = $value;

        return $this;
    }

    /**
     * This method allows us to chain getAttribute calls on related entities.
     * Ex: $person->getAttribute('company')->getAttribute('name')->getValue(); // This will output company name
     *
     * @param $name
     *
     * @return AttributeAbstract
     */
    public function getAttribute($name)
    {
        return $this->getValue()->getAttribute($name);
    }

    /**
     * This method allows us to use simplified access to attributes (no autocomplete).
     * Ex: $person->company->name // Will output company name
     *
     * @param $name
     *
     * @return AttributeAbstract
     */
    public function _get($name)
    {
        return $this->getAttribute($name);
    }

    /**
     * Perform validation against given value
     *
     * @param $value
     *
     * @throws ValidationException
     * @return $this
     */
    public function validate(&$value)
    {
        if (!$this->isNull($value) && !is_array($value) && !$this->isInstanceOf($value, '\Webiny\Component\Entity\EntityAbstract') && !Entity::getInstance()
                                                                                                                                             ->getDatabase()
                                                                                                                                             ->isMongoId($value)
        ) {
            throw new ValidationException(ValidationException::ATTRIBUTE_VALIDATION_FAILED, [
                $this->attribute,
                'entity ID, instance of \Webiny\Component\Entity\EntityAbstract or null',
                gettype($value)
            ]);
        }

        return $this;
    }

    public function onSetNull($callable)
    {
        $this->setNull = $callable;

        return $this;
    }
}