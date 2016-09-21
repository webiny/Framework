<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Entity\Attribute;

use MongoDB\Driver\Exception\BulkWriteException;
use Webiny\Component\Entity\Entity;
use Webiny\Component\Entity\AbstractEntity;
use Webiny\Component\Entity\EntityCollection;
use Webiny\Component\Mongo\Index\CompoundIndex;
use Webiny\Component\Mongo\MongoTrait;


/**
 * One2Many attribute
 * @package Webiny\Component\Entity\AttributeType
 */
class Many2ManyAttribute extends AbstractCollectionAttribute
{
    use MongoTrait;

    protected $intermediateCollection;

    protected $addedItems = [];

    public function __construct($attribute = null, AbstractEntity $entity = null, $collectionName)
    {
        $this->intermediateCollection = $collectionName;
        parent::__construct($attribute, $entity);
    }

    /**
     * Remove item from many2many collection (removes the link between entities)
     *
     * @param string|AbstractEntity $item Entity ID or instance of AbstractEntity
     *
     * @return bool
     */
    public function unlink($item)
    {
        // Unlink item
        $deleted = $this->unlinkItem($item);
        // If values are already loaded - remove deleted item from loaded data set
        if (!$this->isNull($this->value)) {
            $this->value->removeItem($item);
        }

        return $deleted;
    }

    /**
     * Remove all items from man2many collection (removes the links between entities)
     * @return bool
     */
    public function unlinkAll()
    {
        foreach ($this->getValue() as $value) {
            $this->unlinkItem($value);
        }

        // Reset current value
        $this->value = new EntityCollection($this->getEntity());

        return true;
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
     * @param bool  $processCallbacks
     *
     * @return null|EntityCollection
     */
    public function getValue($params = [], $processCallbacks = true)
    {
        if ($this->isNull($this->value)) {
            $this->value = $this->load();
        }

        // Add new items to value and unset these new items
        foreach ($this->addedItems as $item) {
            $this->value[] = $item;
        }
        $this->addedItems = [];

        return $this->processGetValue($this->value, $params, $processCallbacks);
    }

    /**
     * Insert links into DB
     *
     * @throws \Webiny\Component\Entity\EntityException
     * @throws \Webiny\Component\StdLib\StdObject\ArrayObject\ArrayObjectException
     */
    public function save()
    {
        $collectionName = $this->intermediateCollection;
        $firstClassName = $this->extractClassName($this->getParentEntity());
        $secondClassName = $this->extractClassName($this->getEntity());

        // Make sure indexes exist
        $indexOrder = [$firstClassName, $secondClassName];
        list($indexKey1, $indexKey2) = $this->arr($indexOrder)->sort()->val();

        $index = new CompoundIndex($collectionName, [
            $indexKey1 => 1,
            $indexKey2 => 1
        ], false, true);

        Entity::getInstance()->getDatabase()->createIndex($collectionName, $index, ['background' => true]);

        /**
         * Insert values
         */
        $existingIds = [];
        $firstEntityId = $this->getParentEntity()->id;
        foreach ($this->getValue() as $item) {
            if ($item instanceof AbstractEntity && !$item->exists()) {
                $item->save();
            }

            if ($item instanceof AbstractEntity) {
                $secondEntityId = $item->id;
            } else {
                $secondEntityId = $item;
            }

            $existingIds[] = $secondEntityId;

            $data = [
                $firstClassName  => $firstEntityId,
                $secondClassName => $secondEntityId
            ];

            try {
                Entity::getInstance()->getDatabase()->insertOne($collectionName, $this->arr($data)->sortKey()->val());
            } catch (BulkWriteException $e) {
                // Unique index was hit and an exception is thrown - that's ok, means the values are already inserted
                continue;
            }
        }

        /**
         * Remove old links
         */
        $removeQuery = [
            $firstClassName  => $firstEntityId,
            $secondClassName => [
                '$nin' => $existingIds
            ]
        ];
        Entity::getInstance()->getDatabase()->delete($collectionName, $removeQuery);

        /**
         * The value of many2many attribute must be set to 'null' to trigger data reload on next access.
         * If this is not done, we may not have proper links between the 2 entities and it may seem as if data was missing.
         */
        $this->setValue(null);
    }

    /**
     * Load many2many attribute value (prepares MongoCursor, lazy loads data)
     *
     * @return EntityCollection
     */
    protected function load()
    {
        $firstClassName = $this->extractClassName($this->getParentEntity());
        $secondClassName = $this->extractClassName($this->getEntity());

        // Select related IDs from aggregation table
        $query = [
            $firstClassName => $this->getParentEntity()->id
        ];

        $relatedObjects = Entity::getInstance()->getDatabase()->find($this->intermediateCollection, $query, [$secondClassName => 1]);
        $relatedIds = [];
        foreach ($relatedObjects as $rObject) {
            $relatedIds[] = $rObject[$secondClassName];
        }

        // Find all related entities using $relatedIds
        $callable = [
            $this->getEntity(),
            'find'
        ];

        return call_user_func_array($callable, [['id' => ['$in' => $relatedIds]]]);
    }

    /**
     * Unlink given item (only removes the aggregation record)
     *
     * @param string|AbstractEntity $item
     *
     * @return bool
     */
    protected function unlinkItem($item)
    {
        // Convert instance to entity ID
        if ($item instanceof AbstractEntity) {
            $item = $item->id;
        }

        $sourceEntityId = $this->getParentEntity()->id;

        if ($this->isNull($sourceEntityId) || $this->isNull($item)) {
            return false;
        }

        $firstClassName = $this->extractClassName($this->getParentEntity());
        $secondClassName = $this->extractClassName($this->getEntity());
        $query = $this->arr([
            $firstClassName  => $sourceEntityId,
            $secondClassName => $item
        ])->sortKey()->val();

        $res = Entity::getInstance()->getDatabase()->delete($this->intermediateCollection, $query);

        return $res->getDeletedCount() == 1;
    }

    /**
     * Extract short class name from class namespace
     *
     * @param $class
     *
     * @return string
     */
    protected function extractClassName($class)
    {
        if (!$this->isString($class)) {
            $class = get_class($class);
        }

        return $this->str($class)->explode('\\')->last()->val();
    }
}