<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Entity\Attribute;

use Webiny\Component\Entity\Entity;
use Webiny\Component\Entity\AbstractEntity;
use Webiny\Component\Entity\EntityCollection;
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
        if (!in_array($action, ['cascade', 'restrict', 'ignore'])) {
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
     * @param bool  $processCallbacks
     *
     * @return bool|null|AbstractEntity
     */
    public function getValue($params = [], $processCallbacks = true)
    {
        if ($this->isNull($this->value)) {
            // We need to load records from DB - but ONLY if parent record has an ID (otherwise no child records can exist in DB)
            $entityId = $this->parent->id;
            if (empty($entityId)) {
                // No child records exist - return an empty EntityCollection
                $this->value = new EntityCollection($this->entityClass, []);
                $this->dataLoaded = true;
            } else {
                // Try loading child records using parent id
                $query = [$this->relatedAttribute => $entityId];

                // Get optional record filters
                $filters = $this->filter;
                if (is_string($filters) || is_callable($filters)) {
                    $callable = is_string($filters) ? [$this->parent, $filters] : $filters;
                    $filters = call_user_func_array($callable, []);
                }

                // Merge optional filters with main query
                $query = array_merge($query, $filters);
                $this->value = call_user_func_array([$this->entityClass, 'find'], [$query, $this->sorter]);
                $this->dataLoaded = true;
            }
        }

        return $processCallbacks ? $this->processGetValue($this->value, $params) : $this->value;
    }

    public function hasValue()
    {
        if ($this->isNull($this->value)) {
            // We need to count records in DB - but ONLY if parent record has an ID (otherwise no child records can exist in DB)
            $entityId = $this->parent->id;
            if (empty($entityId)) {
                return false;
            }
            $query = [$this->relatedAttribute => $entityId];
            $entityCollection = call_user_func_array([$this->entityClass, 'getCollection'], []);

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

        $attrValues = $this->getValue();
        foreach ($attrValues as $r) {
            if (!in_array($r->id, $newIds)) {
                $r->delete();
            }
        }
    }
}