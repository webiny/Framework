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
use Webiny\Component\Mongo\MongoTrait;


/**
 * One2Many attribute
 * @package Webiny\Component\Entity\AttributeType
 */
class Many2ManyAttribute extends AbstractCollectionAttribute
{
    use MongoTrait;

    /**
     * @var string Collection used to store the aggregation data (ids of linked entities)
     */
    protected $intermediateCollection;

    /**
     * @var string Field name that contains the ID of this (parent) entity
     */
    protected $thisField;

    /**
     * @var string Field name that contains the ID of referenced entity
     */
    protected $refField;

    protected $addedItems = [];

    public function __construct($attribute = null, $thisField = '', $refField = '', AbstractEntity $entity = null, $collectionName)
    {
        $this->intermediateCollection = $collectionName;
        $this->thisField = $thisField;
        $this->refField = $refField;
        parent::__construct($attribute, $entity);
    }

    /**
     * Get field name that points to the entity this attribute belongs to
     *
     * @return string
     */
    public function getThisField()
    {
        return $this->thisField;
    }

    /**
     * Get field name that points to the referenced entity
     *
     * @return string
     */
    public function getRefField()
    {
        return $this->refField;
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
                $this->thisField => $firstEntityId,
                $this->refField  => $secondEntityId
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
            $this->thisField => $firstEntityId,
            $this->refField  => [
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
        // Select related IDs from aggregation table
        $query = [
            $this->thisField => $this->getParentEntity()->id
        ];

        $relatedObjects = Entity::getInstance()->getDatabase()->find($this->intermediateCollection, $query, [$this->refField => 1]);
        $relatedIds = [];
        foreach ($relatedObjects as $rObject) {
            $relatedIds[] = $rObject[$this->refField];
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

        $query = $this->arr([
            $this->thisField => $sourceEntityId,
            $this->refField  => $item
        ])->sortKey()->val();

        $res = Entity::getInstance()->getDatabase()->delete($this->intermediateCollection, $query);

        return $res->getDeletedCount() == 1;
    }
}