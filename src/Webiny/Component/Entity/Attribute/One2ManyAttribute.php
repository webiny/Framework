<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Entity\Attribute;

use Traversable;
use Webiny\Component\Entity\EntityAbstract;
use Webiny\Component\Entity\EntityCollection;
use Webiny\Component\StdLib\StdLibTrait;


/**
 * One2Many attribute
 * @package Webiny\Component\Entity\AttributeType
 */
class One2ManyAttribute extends CollectionAttributeAbstract
{
    use StdLibTrait;

    protected $_relatedAttribute = null;
    protected $_filter = [];
    protected $_onDelete = 'cascade';

    /**
     * @var null|\Webiny\Component\Entity\EntityAbstract
     */
    protected $_entity = null;

    /**
     * @param string         $attribute
     * @param EntityAbstract $entity
     * @param string         $relatedAttribute
     */
    public function __construct($attribute, EntityAbstract $entity, $relatedAttribute)
    {
        $this->_relatedAttribute = $relatedAttribute;
        parent::__construct($attribute, $entity);
    }

    /**
     * Filter returned result set
     *
     * @param array $filter
     *
     * @return $this
     */
    public function setFilter(array $filter)
    {
        $this->_filter = $filter;

        return $this;
    }

    /**
     * Get action to perform when parent entity is being deleted.
     *
     * @return string
     */
    public function getOnDelete()
    {
        return $this->_onDelete;
    }

    /**
     * Set action to perform when parent entity is being deleted.
     *
     * @param string $action cascade|restrict Default value is 'cascade'
     *
     * @return $this
     */
    public function setOnDelete($action = 'cascade')
    {
        if ($action != 'cascade' && $action != 'restrict') {
            $action = 'cascade';
        }

        $this->_onDelete = $action;

        return $this;
    }

    /**
     * Get attribute that defines a foreign key
     */
    public function getRelatedAttribute()
    {
        return $this->_relatedAttribute;
    }

    public function setValue($value = null)
    {
        $this->_value = $value;

        return $this;
    }

    /**
     * Set or get attribute value
     *
     * @return bool|null|\Webiny\Component\Entity\EntityAbstract
     */
    public function getValue()
    {
        if ($this->isNull($this->_value)) {
            $query = [
                $this->_relatedAttribute => $this->_entity->getId()->getValue()
            ];

            $query = array_merge($query, $this->_filter);

            $callable = [
                $this->_entityClass,
                'find'
            ];
            $this->_value = call_user_func_array($callable, [$query]);
        }

        return $this->_value;
    }
}