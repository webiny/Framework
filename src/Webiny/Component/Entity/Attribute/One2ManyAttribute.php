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

    protected $relatedAttribute = null;
    protected $filter = [];
    protected $onDelete = 'cascade';

    /**
     * @var null|\Webiny\Component\Entity\EntityAbstract
     */
    protected $entity = null;

    /**
     * @param string         $attribute
     * @param EntityAbstract $entity
     * @param string         $relatedAttribute
     */
    public function __construct($attribute, EntityAbstract $entity, $relatedAttribute)
    {
        $this->relatedAttribute = $relatedAttribute;
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
        $this->filter = $filter;

        return $this;
    }

    /**
     * Get action to perform when parent entity is being deleted.
     *
     * @return string
     */
    public function getOnDelete()
    {
        return $this->onDelete;
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

        $this->onDelete = $action;

        return $this;
    }

    /**
     * Get attribute that defines a foreign key
     */
    public function getRelatedAttribute()
    {
        return $this->relatedAttribute;
    }

    public function setValue($value = null)
    {
        if(!$this->canAssign()){
            return $this;
        }

        $this->value = $value;

        return $this;
    }

    /**
     * Set or get attribute value
     *
     * @return bool|null|\Webiny\Component\Entity\EntityAbstract
     */
    public function getValue()
    {
        if ($this->isNull($this->value)) {
            $query = [
                $this->relatedAttribute => $this->entity->getId()->getValue()
            ];

            $query = array_merge($query, $this->filter);

            $callable = [
                $this->entityClass,
                'find'
            ];
            $this->value = call_user_func_array($callable, [$query]);
        }

        return $this->value;
    }
}