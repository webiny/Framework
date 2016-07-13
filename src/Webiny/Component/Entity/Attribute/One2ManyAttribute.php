<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Entity\Attribute;

use Webiny\Component\Entity\Attribute\Validation\ValidationException;
use Webiny\Component\Entity\Entity;
use Webiny\Component\Entity\AbstractEntity;
use Webiny\Component\StdLib\StdLibTrait;


/**
 * One2Many attribute
 * @package Webiny\Component\Entity\AttributeType
 */
class One2ManyAttribute extends AbstractCollectionAttribute
{
    use StdLibTrait;

    protected $relatedAttribute = null;
    protected $filter = [];
    protected $sorter = [];
    protected $onDelete = 'cascade';
    protected $dataLoaded = false;

    /**
     * @var null|\Webiny\Component\Entity\AbstractEntity
     */
    protected $parent = null;

    /**
     * @param null|string    $name
     * @param AbstractEntity $parent
     * @param string         $relatedAttribute
     */
    public function __construct($name = null, AbstractEntity $parent = null, $relatedAttribute)
    {
        $this->relatedAttribute = $relatedAttribute;
        parent::__construct($name, $parent);
    }

    public function isLoaded()
    {
        return $this->dataLoaded;
    }

    /**
     * Filter returned result set
     *
     * @param array|callable $filter
     *
     * @return $this
     */
    public function setFilter($filter)
    {
        $this->filter = $filter;

        return $this;
    }

    /**
     * Sort returned result set
     *
     * @param array|string $sorter Ex: ['-order', '+createdOn'] or '-order,+createdOn'
     *
     * @return $this
     */
    public function setSorter($sorter)
    {
        $this->sorter = $this->parseSorter($sorter);

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

            // If new value is being set - delete all existing records that are NOT in the new data set
            $this->cleanUpRecords($value);
            $this->dataLoaded = true;
        }

        $this->value = $value;

        return $this;
    }

    /**
     * Set or get attribute value
     *
     * @param array $params
     *
     * @return bool|null|AbstractEntity
     */
    public function getValue($params = [])
    {
        if ($this->isNull($this->value)) {
            $entityId = $this->parent->id;
            $entityId = empty($entityId) ? '__webiny_dummy_id__' : $entityId;
            $query = [
                $this->relatedAttribute => $entityId
            ];

            $filters = $this->filter;
            if (is_string($filters) || is_callable($filters)) {
                $callable = is_string($filters) ? [$this->parent, $filters] : $filters;
                $filters = call_user_func_array($callable, []);
            }

            $query = array_merge($query, $filters);

            $callable = [
                $this->entityClass,
                'find'
            ];
            $this->value = call_user_func_array($callable, [$query, $this->sorter]);
            $this->dataLoaded = true;
        }

        return $this->processGetValue($this->value, $params);
    }

    public function hasValue()
    {
        if ($this->isNull($this->value)) {
            $entityId = $this->parent->id;
            $entityId = empty($entityId) ? '__webiny_dummy_id__' : $entityId;
            $query = [
                $this->relatedAttribute => $entityId
            ];

            $entityCollection = call_user_func_array([$this->entityClass, 'getEntityCollection'], []);

            return boolval(Entity::getInstance()->getDatabase()->count($entityCollection, $query));
        }

        return boolval($this->value);
    }

    private function parseSorter($fields)
    {
        $sorters = [];

        if (is_string($fields)) {
            $fields = explode(',', $fields);
        }

        foreach ($fields as $sort) {
            $sortField = $sort;
            $sortDirection = 1;

            $sortDirectionSign = substr($sort, 0, 1);
            if ($sortDirectionSign == '+' || $sortDirectionSign == '-') {
                $sortField = substr($sort, 1);
                $sortDirection = $sortDirectionSign == '+' ? 1 : -1;
            }

            $sorters[$sortField] = $sortDirection;
        }

        return $sorters;
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
        $entityClass = $this->getEntity();
        $entityCollectionClass = '\Webiny\Component\Entity\EntityCollection';

        // Validate One2Many attribute value
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

    private function cleanUpRecords($newValues)
    {
        if (!$this->parent->exists()) {
            return;
        }

        $newIds = [];
        foreach ($newValues as $nv) {
            if (isset($nv['id']) && $nv['id'] != '') {
                $newIds[] = Entity::getInstance()->getDatabase()->id($nv['id']);
            }
        }

        $where = [
            '_id' => ['$nin' => $newIds]
        ];

        $where[$this->relatedAttribute] = $this->parent->id;

        $toRemove = call_user_func_array([$this->entityClass, 'find'], [$where]);
        foreach ($toRemove as $r) {
            $r->delete();
        }
    }
}