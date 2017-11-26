<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Entity;

use Webiny\Component\Mongo\MongoTrait;
use Webiny\Component\StdLib\StdLibTrait;


/**
 * EntityCollection is a wrapper for array of entities used by `find` AbstractEntity method
 *
 * @package Webiny\Component\Entity
 */
class EntityCollection implements \IteratorAggregate, \ArrayAccess, \Countable
{
    use MongoTrait, StdLibTrait;

    private $totalCount;
    private $totalCountCalculation;
    private $entityClass;
    private $value = [];
    private $parameters = [];

    /**
     * @param string $entityClass Entity class
     * @param array  $data Array of data to be converted to EntityCollection
     * @param array  $parameters Parameters used to load given data
     */
    public function __construct($entityClass, $data = [], $parameters = [])
    {
        $this->entityClass = $entityClass;
        $this->parameters = $parameters;
        foreach ($data as $d) {
            // Normalize value and skip validation
            $this->value[] = $this->normalizeValue($d, true);
        }
    }

    /**
     * Get string of masked entity values when array of instances is being converted to string
     *
     * @return mixed|null|string
     */
    public function __toString()
    {
        $references = [];
        foreach ($this->value as $item) {
            $references[] = $item->getMaskedValue();
        }

        return $this->arr($references)->implode(', ')->val();
    }

    /**
     * Convert EntityCollection to array.
     * Each AbstractEntity wil be converted to array using $fields which can be defined as a comma-separated keys, or as a function
     * which will be ran with each entity - this gives the possibility to return different data sets depending on the given function
     *
     * @param string $fields List of fields to extract
     *
     * @return array
     */
    public function toArray($fields = '')
    {
        $data = [];
        foreach ($this->value as $entity) {
            $data[] = is_callable($fields) ? $fields($entity) : $entity->toArray($fields);
        }

        return $data;
    }

    /**
     * Count items in entity collection
     *
     * @return mixed
     */
    public function count()
    {
        return count($this->value);
    }

    /**
     * Gets first entity in collection
     * @return AbstractEntity|null
     */
    public function first()
    {
        return $this->value[0] ?? null;
    }

    /**
     * Gets last entity in collection
     * @return AbstractEntity|null
     */
    public function last()
    {
        $lastIndex = $this->count() - 1;

        return $this->value[$lastIndex] ?? null;
    }

    /**
     * Filter current EntityCollection using the given \Closure and return a new array
     *
     * @param \Closure $callback
     *
     * @return array
     */
    public function filter(\Closure $callback)
    {
        $result = [];
        foreach ($this->value as $entity) {
            if ($callback($entity)) {
                $result[] = $entity;
            }
        }

        return $result;
    }

    /**
     * Apply the callback to each entity in the current EntityCollection
     *
     * Returns a new array containing the return values of each callback execution.
     *
     * @param \Closure $callback
     *
     * @return array
     */
    public function map(\Closure $callback)
    {
        $result = [];
        foreach ($this->value as $entity) {
            $result[] = $callback($entity);
        }

        return $result;
    }

    /**
     * Count total number of items in collection
     *
     * @return mixed
     */
    public function totalCount()
    {
        if (!isset($this->parameters['conditions'])) {
            return count($this->value);
        }

        if (!$this->totalCount) {
            if (is_callable($this->totalCountCalculation)) {
                $this->totalCount = ($this->totalCountCalculation)();
            } else {
                $mongo = Entity::getInstance()->getDatabase();
                $entity = $this->entityClass;
                $this->totalCount = $mongo->count($entity::getCollection(), $this->parameters['conditions']);
            }
        }

        return $this->totalCount;
    }

    /**
     * Overrides the default totalCount method.
     *
     * @param callable $callable
     *
     * @return $this
     */
    public function setTotalCountCalculation(callable $callable)
    {
        $this->totalCountCalculation = $callable;

        return $this;
    }

    public function getLimit()
    {
        return $this->parameters['limit'] ?? 0;
    }

    public function getOffset()
    {
        return $this->parameters['offset'] ?? 0;
    }

    /**
     * Check if given item is already in the data set.<br>
     * NOTE: this triggers loading of data from database if not yet loaded
     *
     * @param string|AbstractEntity $item ID or AbstractEntity instance
     *
     * @return bool
     */
    public function contains($item)
    {
        if ($item instanceof AbstractEntity) {
            $item = $item->id;
        }
        foreach ($this->value as $entity) {
            $eId = $entity->id;
            if (!$this->isNull($eId) && $eId == $item) {
                return true;
            }
        }

        return false;
    }

    /**
     * Delete all items in the result set (WARNING: removes data from database)
     * @return bool
     * @throws EntityException
     */
    public function delete()
    {
        foreach ($this->value as $index => $item) {
            $item->delete();
            unset($this->value[$index]);
        }

        return true;
    }

    /**
     * Remove item from data set
     *
     * @param $item
     */
    public function removeItem($item)
    {
        if ($item instanceof AbstractEntity) {
            $item = $item->id;
        }
        foreach ($this->value as $index => $entity) {
            if ($entity->id == $item) {
                unset($this->value[$index]);

                return;
            }
        }
    }

    /**
     * Get collection data
     *
     * @return array
     */
    public function getData()
    {
        return $this->value;
    }

    /**
     * Shuffle values (useful when you want to fetch a random value)
     *
     * @return $this
     */
    public function randomize()
    {
        shuffle($this->value);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->value);
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($offset)
    {
        return isset($this->value[$offset]);
    }

    /**
     * @inheritdoc
     */
    public function offsetGet($offset)
    {
        return $this->value[$offset];
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value)
    {
        $value = $this->normalizeValue($value);
        if ($this->isNull($offset)) {
            $this->value[] = $value;
        } else {
            $this->value[$offset] = $value;
        }
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset)
    {
        unset($this->value[$offset]);
    }

    /**
     * Normalize value to always be an instance of AbstractEntity
     *
     * @param array|AbstractEntity $item
     *
     * @param bool                 $fromDb Is $item coming from DB
     *
     * @return AbstractEntity
     * @throws EntityException
     */
    protected function normalizeValue($item, $fromDb = false)
    {
        $entityClass = $this->entityClass;
        $itemEntity = null;

        // $item can be an array of data or AbstractEntity
        if ($this->isInstanceOf($item, $entityClass)) {
            return $item;
        }

        if ($this->isArray($item) || $this->isArrayObject($item)) {
            if (isset($item['id'])) {
                // Try getting the instance from cache
                $itemEntity = Entity::getInstance()->get($entityClass, $item['id']);
                if ($itemEntity) {
                    return $itemEntity;
                }

                // If not found in cache, load from database
                $itemEntity = $entityClass::findById($item['id']);
            }
        }

        // If instance was not found, create a new entity instance
        if (!$itemEntity) {
            $itemEntity = new $entityClass;
        }

        // If $item is an array - use it to populate the entity instance
        if ($this->isArray($item) || $this->isArrayObject($item)) {
            if ($fromDb) {
                $item['__webiny_db__'] = true;
            }

            $itemEntity->populate($item);
        }

        return $itemEntity;
    }
}