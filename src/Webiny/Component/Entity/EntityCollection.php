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
class EntityCollection implements \IteratorAggregate, \ArrayAccess
{
    use MongoTrait, StdLibTrait;

    /**
     * @var \MongoCursor
     */
    private $cursor;
    private $entityClass;
    private $collectionName;
    private $conditions;
    private $order;
    private $offset;
    private $limit;
    private $count;
    private $value = [];
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
        $this->order = $order;
        $this->offset = $offset;
        $this->limit = $limit;

        $this->cursor = Entity::getInstance()
                              ->getDatabase()
                              ->find($this->collectionName, $this->conditions)
                              ->sort($this->order)
                              ->skip($this->offset)
                              ->limit($this->limit);

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
     * Convert EntityCollection to array.<br>
     * Each EntityAbstract wil be converted to array using $fields and $nestedLevel specified.<br>
     * If no fields are specified, array will contain all simple and Many2One attributes
     *
     * @param string $fields List of fields to extract
     *
     * @param int    $nestedLevel How many levels to extract (Default: 1, means SELF + 1 level)
     *
     * @return array
     */
    public function toArray($fields = '', $nestedLevel = 1)
    {
        $data = [];
        foreach ($this->getIterator() as $entity) {
            $data[] = $entity->toArray($fields, $nestedLevel);
        }

        return $data;
    }

    /**
     * Add item to collection
     *
     * @param array|EntityAbstract $item
     *
     * @return $this
     */
    public function add($item)
    {
        if (!$this->isArray($item)) {
            $item = [$item];
        }

        foreach ($item as $addItem) {
            if (!$this->isInstanceOf($addItem, '\Webiny\Component\Entity\EntityAbstract')) {
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
     * Count items in collection
     * @return mixed
     */
    public function count()
    {
        return $this->cursor->count(true);
    }

    /**
     * Count total number of items without limit and offset
     * @return mixed
     */
    public function totalCount()
    {
        if (!$this->count) {
            $this->count = $this->cursor->count(false);
        }

        return $this->count;
    }

    /**
     * Check if given item is already in the data set.<br>
     * NOTE: this triggers loading of data from database if not yet loaded
     *
     * @param string|EntityAbstract $item ID or EntityAbstract instance
     *
     * @return bool
     */
    public function contains($item)
    {
        if ($this->isInstanceOf($item, '\Webiny\Component\Entity\EntityAbstract')) {
            $item = $item->getId()->getValue();
        }
        foreach ($this->getIterator() as $entity) {
            $eId = $entity->getId()->getValue();
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
            if ($this->isInstanceOf($item, '\Webiny\Component\Entity\EntityAbstract')) {
                $item = $item->getId()->getValue();
            }
            foreach ($this->getIterator() as $index => $entity) {
                if ($entity->getId()->getValue() == $item) {
                    unset($this->value[$index]);

                    return;
                }
            }
        }
    }

    /**
     * Get mongo query explanation
     *
     * @return array
     */
    public function explain()
    {
        return $this->cursor->explain();
    }

    /**
     * Get collection data
     * @return Traversable
     */
    public function getData()
    {
        return $this->getIterator();
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
        foreach ($this->cursor as $data) {
            $instance = new $this->entityClass;
            $data['__webiny_db__'] = true;
            $instance->populate($data);
            /**
             * Check if loaded instance is already in the pool and if yes - use the existing object
             */
            if ($itemInPool = EntityPool::getInstance()->get($this->entityClass, $instance->getId()->getValue())) {
                $dbItems[] = $itemInPool;
            } else {
                $dbItems[] = EntityPool::getInstance()->add($instance);
            }
        }
        $this->value = array_merge($dbItems, $this->value);
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