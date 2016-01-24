<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Entity\Attribute;

use Webiny\Component\Entity\Entity;
use Webiny\Component\Entity\EntityAbstract;
use Webiny\Component\Entity\Validation\ValidationException;
use Webiny\Component\StdLib\StdLibTrait;


/**
 * Many2One attribute
 * @package Webiny\Component\Entity\AttributeType
 */
class Many2OneAttribute extends AttributeAbstract
{
    use StdLibTrait;

    protected $entityClass = null;

    protected $updateExisting = false;

    /**
     * @var null|\Closure
     */
    protected $onSetNullCallback = null;

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
     * Allow update of existing entity
     *
     * By default, only new Many2One records are created but updates are not allowed.
     *
     * @param bool|true $flag
     *
     * @return $this
     */
    public function setUpdateExisting($flag = true)
    {
        $this->updateExisting = $flag;

        return $this;
    }

    /**
     * Is update of existing entity allowed?
     * @return bool
     */
    public function getUpdateExisting()
    {
        return $this->updateExisting;
    }

    /**
     * Get value that will be stored to database
     *
     * @return string
     */
    public function getDbValue()
    {
        if (is_null($this->value)) {
            // Process default value
            $value = $this->defaultValue;
            if ($this->isInstanceOf($value, '\Webiny\Component\Entity\EntityAbstract')) {
                if (!$value->exists()) {
                    $value->save();
                }

                $value = $value->id;
            }

            return $this->processToDbValue($value);
        }

        // Array or string
        if (!$this->isInstanceOf($this->value, $this->entityClass) && !empty($this->value)) {
            if ($this->isArray($this->value)) {
                $id = isset($data['id']) ? $data['id'] : null;
                if (!$this->getUpdateExisting()) {
                    return $this->processToDbValue($id);
                } elseif ($this->getUpdateExisting() && Entity::getInstance()->getDatabase()->isMongoId($id)) {
                    $this->value = $this->loadEntity($id);
                    if ($this->value) {
                        $this->value->populate($data)->save();
                    } elseif ($data) {
                        $this->value = new $this->entityClass;
                        $this->value->populate($data)->save();
                    }

                    return $this->processToDbValue($this->value->id);
                }
            }

            // String ID
            return $this->processToDbValue($this->value);
        }

        // Entity value
        if (!$this->value->exists() || $this->getUpdateExisting()) {
            $this->value->save();
        }

        return $this->processToDbValue($this->value->id);
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

            $this->value = $this->loadEntity($this->value);

            if ($this->value) {
                $this->value->populate($data);
            } elseif ($data) {
                $this->value = new $this->entityClass;
                $this->value->populate($data);
            }
        }

        if (!$this->value && !$this->isNull($this->defaultValue)) {
            return $this->processGetValue($this->defaultValue);
        }

        return $this->processGetValue($this->value);
    }

    /**
     * Set attribute value
     *
     * @param null $value
     * @param bool $fromDb
     *
     * @return $this
     * @throws \Webiny\Component\Entity\EntityException
     */
    public function setValue($value = null, $fromDb = false)
    {
        if ($fromDb) {
            $this->value = $value;

            return $this;
        }

        if (!$this->canAssign()) {
            return $this;
        }

        $id = null;
        $data = [];
        $entity = null;

        // Normalize $value using one of 5 scenarios:
        // 1: EntityAbstract instance
        // 2: ID
        // 3: Array with ID
        // 4: Array without ID
        // 5: null (default)
        if ($this->isInstanceOf($value, $this->entityClass)) {
            $id = $value->id;
            $entity = $value;
        } elseif (Entity::getInstance()->getDatabase()->isMongoId($value)) {
            $id = $value;
        } elseif (is_array($value) && isset($value['id'])) {
            $id = $value['id'];
            $data = $value;
            unset($data['id']);
        } elseif (is_array($value) && !isset($value['id'])) {
            $entity = new $this->entityClass;
            $data = $value;
        } else {
            $entity = $value;
        }

        // Try loading entity with existing ID if not already assigned
        if ($id && !$entity) {
            $entity = $this->loadEntity($id);
        }

        // Optionally, populate entity with new data
        if ($this->isInstanceOf($entity, $this->entityClass) && (!$entity->exists() || $this->updateExisting)) {
            $entity->populate($data);
        }

        // Process onSet() callback
        $previousValue = $this->getValue();
        $value = $this->processSetValue($entity);

        $this->validate($value);

        $this->value = $value;

        // Execute setNull callback
        if ($this->onSetNullCallback && is_null($this->value) && $previousValue) {
            $callable = $this->onSetNullCallback;
            if ($callable == 'delete') {
                $previousValue->delete();
            } else {
                $callable($previousValue);
            }
        }

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
    protected function validate(&$value)
    {
        $mongoId = Entity::getInstance()->getDatabase()->isMongoId($value);
        $abstractEntityClass = '\Webiny\Component\Entity\EntityAbstract';

        if (!$this->isNull($value) && !is_array($value) && !$this->isInstanceOf($value, $abstractEntityClass) && !$mongoId) {
            $this->expected('entity ID, instance of ' . $abstractEntityClass . ' or null', gettype($value));
        }

        return $this;
    }

    public function onSetNull($callable)
    {
        $this->onSetNullCallback = $callable;

        return $this;
    }

    private function loadEntity($id)
    {
        if (!$id) {
            return null;
        }

        return call_user_func_array([
            $this->entityClass,
            'findById'
        ], [$id]);
    }
}