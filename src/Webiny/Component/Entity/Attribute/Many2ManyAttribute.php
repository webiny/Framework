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
use Webiny\Component\StdLib\StdLibTrait;


/**
 * One2Many attribute
 * @package Webiny\Component\Entity\AttributeType
 */
class Many2ManyAttribute extends CollectionAttributeAbstract
{
    protected $_intermediateCollection;

    protected $_addedItems = [];

    public function __construct($attribute, EntityAbstract $entity, $collectionName)
    {
        $this->_intermediateCollection = $collectionName;
        parent::__construct($attribute, $entity);
    }

    /**
     * Add item to this entity collection<br>
     * NOTE: you need to call save() on parent entity to actually insert link into database
     *
     * @param array|\Webiny\Component\Entity\EntityAbstract $item
     *
     * @throws \Webiny\Component\Entity\EntityException
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
                throw new EntityException(EntityException::INVALID_MANY2MANY_VALUE, [
                        $this->_attribute,
                        'entity ID or instance of ' . $this->getEntity() . ' or null',
                        get_class($i)
                    ]
                );
            }
        }

        /**
         * Assign items
         */
        foreach ($item as $i) {
            $this->_addedItems[] = $i;
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
        if (!$this->isNull($this->_value)) {
            $this->_value->removeItem($item);
        }

        return $deleted;
    }

    /**
     * Get collection that holds entity references
     */
    public function getIntermediateCollection()
    {
        return $this->_intermediateCollection;
    }

    /**
     * Set or get attribute value
     *
     * @param null $value
     *
     * @return $this|null|EntityCollection
     */
    public function setValue($value = null)
    {
        if(!$this->_canAssign()){
            return $this;
        }

        $this->_value = $value;

        return $this;
    }

    /**
     * Get attribute value
     *
     * @return null|EntityCollection
     */
    public function getValue()
    {
        if ($this->isNull($this->_value)) {
            $this->_value = Many2ManyStorage::getInstance()->load($this);
            // Add new items to _value and unset these new items
            foreach ($this->_addedItems as $item) {
                $this->_value->add($item);
            }
            $this->_addedItems = [];
        }

        return $this->_value;
    }
}