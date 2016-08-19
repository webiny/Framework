<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Entity;

use Traversable;
use Webiny\Component\Mongo\MongoTrait;
use Webiny\Component\StdLib\StdLibTrait;
use Webiny\Component\StdLib\StdObject\StdObjectWrapper;


/**
 * EntityCollection class holds parameters for `find`
 *
 * @package Webiny\Component\Entity
 */
class EntityCollection implements \IteratorAggregate, \ArrayAccess, \Countable
{
    use MongoTrait, StdLibTrait;

    private $entityClass;
    private $collectionName;
    private $data = [];
    private $totalCount;
    private $value = [];
    private $conditions = [];
    private $loaded = false;
    private $randomize = false;

    public function __construct($entityClass, $entityCollection, $conditions, $order, $limit, $offset)
    {
        // Convert boolean strings to boolean
        foreach ($conditions as &$condition) {
            if (is_scalar($condition) && (strtolower($condition) === 'true' || strtolower($condition) === 'false')) {
                $condition = StdObjectWrapper::toBool($condition);
            }
        }

        $this->entityClass = $entityClass;
        $this->collectionName = $entityCollection;
        $this->conditions = $conditions;
        $this->limit = $limit;
        $this->offset = $offset;

        $this->data = Entity::getInstance()->getDatabase()->find($entityCollection, $conditions, $order, $limit, $offset);
    }

    /**
     * Get string of masked entity values when array of instances is being converted to string
     *
     * @return mixed|null|string
     */
    public function __toString()
    {
        $references = [];
        foreach ($this->getIterator() as $item) {
            $references[] = $item->getMaskedValue();
        }

        return $this->arr($references)->implode(', ')->val();
    }

    /**
     * Get collection limit
     * @return mixed
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * Get collection offset
     * @return mixed
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * Convert EntityCollection to array.
     * Each AbstractEntity wil be converted to array using $fields which can be defined as a comma-separated keys, or as a function
     * which will be ran with each entity - this gives the possibility to return different data sets depending on the given function
     * @param string $fields List of fields to extract
     *
     * @return array
     */
    public function toArray($fields = '')
    {
        $data = [];
        foreach ($this->getIterator() as $entity) {
            $data[] = is_callable($fields) ? $fields($entity) : $entity->toArray($fields);
        }

        return $data;
    }

    /**
     * Add item to collection
     *
     * @param array|AbstractEntity $item
     *
     * @return $this
     */
    public function add($item)
    {
        if (!$this->isArray($item)) {
            $item = [$item];
        }

        foreach ($item as $addItem) {
            if (!$this->isInstanceOf($addItem, '\Webiny\Component\Entity\AbstractEntity')) {
                $class = $this->entityClass;
                $addItem = $class::findById($addItem);
            }

            if (!$this->contains($addItem)) {
                $this->value[] = $addItem;
            }
        }

        return $this;
    }

    /**
     * Count items in entity collection
     *
     * @return mixed
     */
    public function count()
    {
        return count(iterator_to_array($this->getIterator()));
    }

    /**
     * Gets first entity in collection
     * @return AbstractEntity|null
     */
    public function first()
    {
        return $this->getIterator()[0] ?? null;
    }

    /**
     * Gets last entity in collection
     * @return AbstractEntity|null
     */
    public function last()
    {
        $lastIndex = $this->getIterator()->count() - 1;

        return $this->getIterator()[$lastIndex] ?? null;
    }

    /**
     * Count total number of items without limit and offset
     *
     * TODO: unittest
     *
     * @return mixed
     */
    public function totalCount()
    {
        if (!$this->totalCount) {
            $mongo = Entity::getInstance()->getDatabase();
            $this->totalCount = $mongo->count($this->collectionName, $this->conditions);
        }

        return $this->totalCount;
    }

    /**
     * Check if given item is already in the data set.<br>
     * NOTE: this triggers loading of data from database if not yet loaded
     *
     * TODO: unittest
     *
     * @param string|AbstractEntity $item ID or AbstractEntity instance
     *
     * @return bool
     */
    public function contains($item)
    {
        if ($this->isInstanceOf($item, '\Webiny\Component\Entity\AbstractEntity')) {
            $item = $item->id;
        }
        foreach ($this->getIterator() as $entity) {
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
        foreach ($this->getIterator() as $index => $item) {
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
        if ($this->loaded) {
            if ($this->isInstanceOf($item, '\Webiny\Component\Entity\AbstractEntity')) {
                $item = $item->id;
            }
            foreach ($this->getIterator() as $index => $entity) {
                if ($entity->id == $item) {
                    unset($this->value[$index]);

                    return;
                }
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
        return iterator_to_array($this->getIterator());
    }

    public function randomize()
    {
        $this->randomize = true;

        return $this;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     */
    public function getIterator()
    {
        if ($this->loaded) {
            return new \ArrayIterator($this->value);
        }

        $dbItems = [];
        foreach ($this->data as $data) {
            $instance = new $this->entityClass;
            $data['__webiny_db__'] = true;
            $instance->populate($data);
            /**
             * Check if loaded instance is already in the pool and if yes - use the existing object
             */
            if ($itemInPool = Entity::getInstance()->get($this->entityClass, $instance->id)) {
                $dbItems[] = $itemInPool;
            } else {
                $dbItems[] = Entity::getInstance()->add($instance);
            }
        }
        $this->value += $dbItems;
        $this->loaded = true;

        if ($this->randomize) {
            shuffle($this->value);
        }

        return new \ArrayIterator($this->value);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param mixed $offset <p>
     *                      An offset to check for.
     *                      </p>
     *
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset)
    {
        return isset($this->getIterator()[$offset]);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param mixed $offset <p>
     *                      The offset to retrieve.
     *                      </p>
     *
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        return $this->getIterator()[$offset];
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param mixed $offset <p>
     *                      The offset to assign the value to.
     *                      </p>
     * @param mixed $value <p>
     *                      The value to set.
     *                      </p>
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->getIterator()[$offset] = $value;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param mixed $offset <p>
     *                      The offset to unset.
     *                      </p>
     *
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->getIterator()[$offset]);
    }
}