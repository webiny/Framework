<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Entity\Attribute;

use Webiny\Component\Entity\AttributeStorage\Many2ManyStorage;
use Webiny\Component\Entity\Entity;
use Webiny\Component\Entity\AbstractEntity;
use Webiny\Component\Entity\EntityCollection;
use Webiny\Component\Entity\Attribute\Validation\ValidationException;


/**
 * One2Many attribute
 * @package Webiny\Component\Entity\AttributeType
 */
class Many2ManyAttribute extends AbstractCollectionAttribute
{
    protected $intermediateCollection;

    protected $addedItems = [];

    public function __construct($attribute = null, AbstractEntity $entity = null, $collectionName)
    {
        $this->intermediateCollection = $collectionName;
        parent::__construct($attribute, $entity);
    }

    /**
     * Add item to this entity collection<br>
     * NOTE: you need to call save() on parent entity to actually insert link into database
     *
     * @param array|\Webiny\Component\Entity\AbstractEntity $item
     *
     * @return $this
     */
    public function add($item)
    {
        if ($this->isInstanceOf($item, '\Webiny\Component\Entity\AbstractEntity')) {
            $item = [$item];
        }

        /**
         * Validate items
         */
        foreach ($item as $i) {
            if (!$this->isInstanceOf($i, $this->getEntity()) && !Entity::getInstance()->getDatabase()->isId($i)) {
                $this->expected('entity ID or instance of ' . $this->getEntity() . ' or null', get_class($i));
            }
        }

        /**
         * Assign items
         */
        foreach ($item as $i) {
            $this->addedItems[] = $i;
        }

        return $this;
    }

    /**
     * Remove item from many2many collection (Removes the link between entities)
     *
     * @param string|AbstractEntity $item Entity ID or instance of AbstractEntity
     *
     * @return bool
     */
    public function remove($item)
    {
        // Unlink item
        $deleted = Many2ManyStorage::getInstance()->unlink($this, $item);
        // If values are already loaded - remove deleted item from loaded data set
        if (!$this->isNull($this->value)) {
            $this->value->removeItem($item);
        }

        // Rebuild cursor (need to call this to rebuild cursor object with new linked IDs)
        $this->value = Many2ManyStorage::getInstance()->load($this);

        return $deleted;
    }

    /**
     * Get collection that holds entity references
     */
    public function getIntermediateCollection()
    {
        return $this->intermediateCollection;
    }

    /**
     * Set or get attribute value
     *
     * @param null $value
     * @param bool $fromDb
     *
     * @return $this|EntityCollection
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

        if (!$fromDb) {
            $value = $this->processSetValue($value);
            $value = $this->normalizeValue($value);
            $this->validate($value);
        }

        $this->value = $value;

        return $this;
    }

    public function hasValue()
    {
        return count($this->getValue()) > 0;
    }

    /**
     * Get attribute value
     *
     * @param array $params
     *
     * @return null|EntityCollection
     */
    public function getValue($params = [])
    {
        if ($this->isNull($this->value)) {
            $this->value = Many2ManyStorage::getInstance()->load($this);
        }

        // Add new items to value and unset these new items
        foreach ($this->addedItems as $item) {
            if ($this->value instanceof EntityCollection) {
                $this->value->add($item);
            } else {
                $this->value[] = $item;
            }

        }
        $this->addedItems = [];

        return $this->processGetValue($this->value, $params);
    }

    /**
     * Normalize given value to be a valid array of entity instances
     *
     * @param mixed $value
     *
     * @return array
     * @throws ValidationException
     */
    private function normalizeValue($value)
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

        return $values;
    }
}