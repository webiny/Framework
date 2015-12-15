<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Entity\Attribute;

use Webiny\Component\Entity\AttributeStorage\Many2ManyStorage;
use Webiny\Component\Entity\Entity;
use Webiny\Component\Entity\EntityAbstract;
use Webiny\Component\Entity\EntityCollection;
use Webiny\Component\Entity\EntityException;


/**
 * One2Many attribute
 * @package Webiny\Component\Entity\AttributeType
 */
class Many2ManyAttribute extends CollectionAttributeAbstract
{
    protected $intermediateCollection;

    protected $addedItems = [];

    public function __construct($attribute, EntityAbstract $entity, $collectionName)
    {
        $this->intermediateCollection = $collectionName;
        parent::__construct($attribute, $entity);
    }

    /**
     * Add item to this entity collection<br>
     * NOTE: you need to call save() on parent entity to actually insert link into database
     *
     * @param array|\Webiny\Component\Entity\EntityAbstract $item
     *
     * @return $this
     */
    public function add($item)
    {
        if ($this->isInstanceOf($item, '\Webiny\Component\Entity\EntityAbstract')) {
            $item = [$item];
        }

        /**
         * Validate items
         */
        foreach ($item as $i) {
            if (!$this->isInstanceOf($i, $this->getEntity()) && !Entity::getInstance()->getDatabase()->isMongoId($i)) {
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
     * @param string|EntityAbstract $item Entity ID or instance of EntityAbstract
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
     * @return $this|null|EntityCollection
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
        }

        $this->value = $value;

        return $this;
    }

    public function hasValue()
    {
        return boolval(Many2ManyStorage::getInstance()->count($this));
    }

    /**
     * Get attribute value
     *
     * @return null|EntityCollection
     */
    public function getValue()
    {
        if ($this->isNull($this->value)) {
            $this->value = Many2ManyStorage::getInstance()->load($this);
            // Add new items to value and unset these new items
            foreach ($this->addedItems as $item) {
                $this->value->add($item);
            }
            $this->addedItems = [];
        }

        return $this->processGetValue($this->value);
    }
}