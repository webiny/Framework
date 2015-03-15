<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Entity\AttributeStorage;

use Webiny\Component\Entity\Attribute\Many2ManyAttribute;
use Webiny\Component\Entity\Entity;
use Webiny\Component\Entity\EntityAbstract;
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

    public function load(Many2ManyAttribute $attribute)
    {
        $firstClassName = $this->extractClassName($attribute->getParentEntity());
        $secondClassName = $this->extractClassName($attribute->getEntity());

        // Select related IDs from aggregation table
        $query = [
            $firstClassName => $attribute->getParentEntity()->getId()->getValue()
        ];

        $relatedObjects = Entity::getInstance()->getDatabase()->find($attribute->getIntermediateCollection(), $query,
                                                                     [$secondClassName]
        );
        $relatedIds = [];
        foreach ($relatedObjects as $rObject) {
            $relatedIds[] = Entity::getInstance()->getDatabase()->id($rObject[$secondClassName]);
        }

        // Find all related entities using $relatedIds
        $query = [
            '_id' => ['$in' => $relatedIds]
        ];

        $callable = [
            $attribute->getEntity(),
            'find'
        ];

        return call_user_func_array($callable, [$query]);
    }

    public function save(Many2ManyAttribute $attribute)
    {
        $collectionName = $attribute->getIntermediateCollection();
        $firstClassName = $this->extractClassName($attribute->getParentEntity());
        $secondClassName = $this->extractClassName($attribute->getEntity());

        // Ensure index
        list($indexKey1, $indexKey2) = $this->arr([
                                                      $firstClassName,
                                                      $secondClassName
                                                  ]
        )->sort()->val();
        $index = [
            $indexKey1 => 1,
            $indexKey2 => 1
        ];

        Entity::getInstance()->getDatabase()->ensureIndex($collectionName, $index, ['unique' => 1]);

        /**
         * Insert values
         */
        foreach ($attribute->getValue() as $item) {
            $firstEntityId = $attribute->getParentEntity()->getId()->getValue();
            if ($item->getId()->getValue() === null) {
                $item->save();
            }
            $secondEntityId = $item->getId()->getValue();
            $query = $this->arr([
                                    $firstClassName  => $firstEntityId,
                                    $secondClassName => $secondEntityId
                                ]
            )->sortKey()->val();

            try {
                Entity::getInstance()->getDatabase()->insert($collectionName, $query);
            } catch (\MongoException $e) {
                continue;
            }
        }
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
     * @param string|EntityAbstract $item
     *
     * @return bool
     */

    public function unlink(Many2ManyAttribute $attribute, $item)
    {
        // Convert instance to entity ID
        if ($this->isInstanceOf($item, '\Webiny\Component\Entity\EntityAbstract')) {
            $item = $item->getId()->getValue();
        }

        $sourceEntityId = $attribute->getParentEntity()->getId()->getValue();

        if ($this->isNull($sourceEntityId) || $this->isNull($item)) {
            return;
        }

        $firstClassName = $this->extractClassName($attribute->getParentEntity());
        $secondClassName = $this->extractClassName($attribute->getEntity());
        $query = $this->arr([
                                $firstClassName  => $sourceEntityId,
                                $secondClassName => $item
                            ]
        )->sortKey()->val();

        $res = Entity::getInstance()->getDatabase()->remove($attribute->getIntermediateCollection(), $query);

        return $res['n'] == 1;
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