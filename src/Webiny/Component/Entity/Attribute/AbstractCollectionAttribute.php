<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Entity\Attribute;

use Webiny\Component\Entity\AbstractEntity;
use Webiny\Component\Entity\Attribute\Validation\ValidationException;
use Webiny\Component\Entity\Entity;
use Webiny\Component\Entity\EntityCollection;
use Webiny\Component\Entity\EntityException;
use Webiny\Component\StdLib\StdLibTrait;


/**
 * AbstractCollectionAttribute
 * @package Webiny\Component\Entity\AttributeType
 */
abstract class AbstractCollectionAttribute extends AbstractAttribute implements \IteratorAggregate, \ArrayAccess
{
    use StdLibTrait;

    protected $entityClass;

    /**
     * @var null|EntityCollection
     */
    protected $value = null;

    /**
     * Get string of masked entity values when array of instances is being converted to string
     *
     * @return mixed|null|string
     */
    /**
     * Get string of masked entity values when array of instances is being converted to string
     *
     * @return mixed|null|string
     */
    public function __toString()
    {
        $references = [];
        foreach ($this->getValue() as $item) {
            $references[] = $item->getMaskedValue();
        }

        return $this->arr($references)->implode(', ')->val();
    }

    /**
     * Count items in result set
     * @return int
     */
    public function count()
    {
        return $this->getValue()->count();
    }

    /**
     * Delete all items in the result set
     * @return bool
     * @throws EntityException
     */
    public function delete()
    {
        return $this->getValue()->delete();
    }

    /**
     * Set related entity class for this attribute
     *
     * @param string $entityClass
     *
     * @return $this
     * @throws EntityException
     */
    public function setEntity($entityClass)
    {
        class_exists($entityClass);
        $this->entityClass = $entityClass;

        return $this;
    }

    /**
     * Get related entity class for this attribute
     *
     * @return string
     */
    public function getEntity()
    {
        return $this->entityClass;
    }

    /**
     * Returns entity instance to which this attribute belongs
     * @return AbstractEntity
     */
    public function getParentEntity()
    {
        return $this->parent;
    }

    /**
     * @inheritdoc
     */
    public function getIterator()
    {
        return $this->getValue();
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($offset)
    {
        return isset($this->getValue()[$offset]);
    }

    /**
     * @inheritdoc
     */
    public function offsetGet($offset)
    {
        return $this->getValue()[$offset];
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value)
    {
        if ($this->isNull($offset))
        {
            $this->getValue()[] = $value;
        } else {
            $this->getValue()[$offset] = $value;
        }
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset)
    {
        unset($this->getValue()[$offset]);
    }

    /**
     * Normalize given value to be a valid array of entity instances
     *
     * @param mixed $value
     *
     * @return array
     * @throws ValidationException
     */
    protected function normalizeValue($value)
    {
        if (is_null($value)) {
            return $value;
        }

        $entityClass = $this->getEntity();
        $entityCollectionClass = '\Webiny\Component\Entity\EntityCollection';

        // Validate Many2many attribute value
        if (!$this->isArray($value) && !$this->isArrayObject($value) && !$this->isInstanceOf($value, $entityCollectionClass)) {
            $exception = new ValidationException(ValidationException::DATA_TYPE, [
                'array, ArrayObject or EntityCollection',
                gettype($value)
            ]);
            $exception->setAttribute($this->getName());
            throw $exception;
        }

        /* @var $entityAttribute One2ManyAttribute */
        $values = [];
        foreach ($value as $item) {
            $itemEntity = false;

            // $item can be an array of data, AbstractEntity or a simple mongo ID string
            if ($this->isInstanceOf($item, $entityClass)) {
                $itemEntity = $item;
            } elseif ($this->isArray($item) || $this->isArrayObject($item)) {
                $itemEntity = $entityClass::findById(isset($item['id']) ? $item['id'] : false);
            } elseif ($this->isString($item) && Entity::getInstance()->getDatabase()->isId($item)) {
                $itemEntity = $entityClass::findById($item);
            }

            // If instance was not found, create a new entity instance
            if (!$itemEntity) {
                $itemEntity = new $entityClass;
            }

            // If $item is an array - use it to populate the entity instance
            if ($this->isArray($item) || $this->isArrayObject($item)) {
                $itemEntity->populate($item);
            }

            $values[] = $itemEntity;
        }

        return new EntityCollection($this->getEntity(), $values);
    }
}