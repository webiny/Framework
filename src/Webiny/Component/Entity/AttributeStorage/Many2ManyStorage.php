<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Entity\AttributeStorage;

use MongoDB\Driver\Exception\BulkWriteException;
use Webiny\Component\Entity\Attribute\Many2ManyAttribute;
use Webiny\Component\Entity\Entity;
use Webiny\Component\Entity\AbstractEntity;
use Webiny\Component\Entity\EntityCollection;
use Webiny\Component\Mongo\Index\CompoundIndex;
use Webiny\Component\Mongo\MongoTrait;
use Webiny\Component\StdLib\SingletonTrait;
use Webiny\Component\StdLib\StdLibTrait;

/**
 * Many2ManyStorage
 * @package \Webiny\Component\Entity\AttributeStorage
 */
class Many2ManyStorage
{
    use StdLibTrait, SingletonTrait, MongoTrait;

    /**
     * Load many2many attribute value (prepares MongoCursor, lazy loads data)
     *
     * @param Many2ManyAttribute $attribute
     *
     * @return EntityCollection
     */
    public function load(Many2ManyAttribute $attribute)
    {
        $firstClassName = $this->extractClassName($attribute->getParentEntity());
        $secondClassName = $this->extractClassName($attribute->getEntity());

        // Select related IDs from aggregation table
        $query = [
            $firstClassName => $attribute->getParentEntity()->id
        ];

        $relatedObjects = Entity::getInstance()
                                ->getDatabase()
                                ->find($attribute->getIntermediateCollection(), $query, [$secondClassName => 1]);
        $relatedIds = [];
        foreach ($relatedObjects as $rObject) {
            $relatedIds[] = $rObject[$secondClassName];
        }

        // Find all related entities using $relatedIds
        $query = [
            'id' => ['$in' => $relatedIds]
        ];

        $callable = [
            $attribute->getEntity(),
            'find'
        ];

        return call_user_func_array($callable, [$query]);
    }

    /**
     * Count many2many attribute values
     *
     * @param Many2ManyAttribute $attribute
     *
     * @return EntityCollection
     */
    public function count(Many2ManyAttribute $attribute)
    {
        $firstClassName = $this->extractClassName($attribute->getParentEntity());

        // Select related IDs from aggregation table
        $query = [
            $firstClassName => $attribute->getParentEntity()->id
        ];

        return Entity::getInstance()->getDatabase()->count($attribute->getIntermediateCollection(), $query);
    }

    public function save(Many2ManyAttribute $attribute)
    {
        $collectionName = $attribute->getIntermediateCollection();
        $firstClassName = $this->extractClassName($attribute->getParentEntity());
        $secondClassName = $this->extractClassName($attribute->getEntity());

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
        $firstEntityId = $attribute->getParentEntity()->id;
        foreach ($attribute->getValue() as $item) {
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
        $attribute->setValue(null);
    }

    /**
     * Unlink given item (only removes the aggregation record) and remove it from current loaded values
     *
     * @param Many2ManyAttribute    $attribute
     * @param string|AbstractEntity $item
     *
     * @return bool
     */

    public function unlink(Many2ManyAttribute $attribute, $item)
    {
        // Convert instance to entity ID
        if ($this->isInstanceOf($item, '\Webiny\Component\Entity\AbstractEntity')) {
            $item = $item->id;
        }

        $sourceEntityId = $attribute->getParentEntity()->id;

        if ($this->isNull($sourceEntityId) || $this->isNull($item)) {
            return false;
        }

        $firstClassName = $this->extractClassName($attribute->getParentEntity());
        $secondClassName = $this->extractClassName($attribute->getEntity());
        $query = $this->arr([
            $firstClassName  => $sourceEntityId,
            $secondClassName => $item
        ])->sortKey()->val();

        $res = Entity::getInstance()->getDatabase()->delete($attribute->getIntermediateCollection(), $query);

        return $res->getDeletedCount() == 1;
    }

    /**
     * Extract short class name from class namespace
     *
     * @param $class
     *
     * @return string
     */
    private function extractClassName($class)
    {
        if (!$this->isString($class)) {
            $class = get_class($class);
        }

        return $this->str($class)->explode('\\')->last()->val();
    }

}