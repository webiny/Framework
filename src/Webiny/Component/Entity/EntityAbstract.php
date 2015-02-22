<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Entity;

use Webiny\Component\Entity\Attribute\AttributeAbstract;
use Webiny\Component\Entity\Attribute\AttributeType;
use Webiny\Component\Entity\Attribute\CharAttribute;
use Webiny\Component\Entity\Attribute\Many2ManyAttribute;
use Webiny\Component\Entity\Attribute\One2ManyAttribute;
use Webiny\Component\Entity\Attribute\ValidationException;
use Webiny\Component\Entity\AttributeStorage\Many2ManyStorage;
use Webiny\Component\StdLib\StdLibTrait;
use Webiny\Component\StdLib\StdObject\ArrayObject\ArrayObject;

/**
 * Entity
 * @package \Webiny\Component\Entity
 */
abstract class EntityAbstract implements \ArrayAccess
{
    use StdLibTrait;

    /**
     * Entity attributes
     * @var ArrayObject
     */
    protected $_attributes;

    /**
     * @var string Entity collection name
     */
    protected static $_entityCollection = null;

    /**
     * View mask (used for grids and many2one input fields)
     * @var string
     */
    protected static $_entityMask = '{id}';

    private $_dirty = false;

    /**
     * This method is called during instantiation to build entity structure
     * @return void
     */
    protected abstract function _entityStructure();

    /**
     * Find entity by ID
     *
     * @param $id
     *
     * @return null|EntityAbstract
     */
    public static function findById($id)
    {
        if (!$id || strlen($id) != 24) {
            return null;
        }
        $instance = EntityPool::getInstance()->get(get_called_class(), $id);
        if ($instance) {
            return $instance;
        }
        $data = Entity::getInstance()
                      ->getDatabase()
                      ->findOne(static::$_entityCollection, ["_id" => Entity::getInstance()->getDatabase()->id($id)]);
        if (!$data) {
            return null;
        }
        $data = EntityMongoAdapter::getInstance()->adaptValues($data);
        $instance = new static;
        $instance->populate($data)->__setDirty(false);

        return EntityPool::getInstance()->add($instance);
    }

    /**
     * Find entity by array of conditions
     *
     * @param array $conditions
     *
     * @return null|EntityAbstract
     * @throws EntityException
     */
    public static function findOne(array $conditions = [])
    {
        if (array_key_exists('id', $conditions)) {
            return self::findById($conditions['id']);
        }

        $data = Entity::getInstance()
                      ->getDatabase()
                      ->findOne(static::$_entityCollection, $conditions);
        if (!$data) {
            return null;
        }
        $data = EntityMongoAdapter::getInstance()->adaptValues($data);
        $instance = new static;
        $instance->populate($data)->__setDirty(false);

        return EntityPool::getInstance()->add($instance);
    }

    /**
     * Find entities
     *
     * @param mixed $conditions
     *
     * @param array $order Example: ['-name', '+title']
     * @param int   $limit
     * @param int   $page
     *
     * @return EntityCollection
     */
    public static function find(array $conditions = [], array $order = [], $limit = 0, $page = 0)
    {
        /**
         * Convert order parameters to Mongo format
         */
        $order = self::_parseOrderParameters($order);
        $offset = $limit * ($page > 0 ? $page - 1 : 0);

        return new EntityCollection(get_called_class(), static::$_entityCollection, $conditions, $order, $limit, $offset
        );
    }

    /**
     * Entity constructor
     */
    public function __construct()
    {
        $this->_attributes = $this->arr();
        $this->_entityStructure();

        /**
         * Add ID to the list of attributes
         */
        $this->attr('id')->char();
    }

    /**
     * @param $attribute
     *
     * @return EntityAttributeBuilder
     */
    public function attr($attribute)
    {
        return EntityAttributeBuilder::getInstance()->__setContext($this->_attributes, $attribute)->__setEntity($this);
    }

    /**
     * Convert EntityAbstract to array with specified fields.
     * If no fields are specified, array will contain all simple and Many2One attributes
     *
     * @param string $fields List of fields to extract
     *
     * @param int    $nestedLevel How many levels to extract (Default: 1, means SELF + 1 level)
     *
     * @return array
     */
    public function toArray($fields = '', $nestedLevel = 1)
    {
        $dataExtractor = new EntityDataExtractor($this, $nestedLevel);

        return $dataExtractor->extractData($fields);
    }


    /**
     * Return string representation of entity
     * @return mixed
     */
    public function __toString()
    {
        return $this->getMaskedValue();
    }

    /**
     * Set entity's dirty flag
     *
     * NOTE: you should not be calling this method on your own!
     *
     * @param bool $flag
     *
     * @return $this|bool
     */
    public function __setDirty($flag = true)
    {
        $this->_dirty = boolval($flag);

        return $this;
    }

    /**
     * Get entity's dirty flag
     *
     * @return bool
     */
    public function __getDirty()
    {
        return $this->_dirty;
    }

    /**
     * Get entity attribute
     *
     * @param string $attribute
     *
     * @throws EntityException
     * @return AttributeAbstract
     */
    public function getAttribute($attribute)
    {
        if (!$this->_attributes->keyExists($attribute)) {
            throw new EntityException(EntityException::ATTRIBUTE_NOT_FOUND, [
                                                                              $attribute,
                                                                              get_class($this)
                                                                          ]
            );
        }

        return $this->_attributes[$attribute];
    }

    /**
     * Get all entity attributes
     *
     * @return ArrayObject
     */
    public function getAttributes()
    {
        return $this->_attributes;
    }

    /**
     * Get entity ID
     * @return CharAttribute
     */
    public function getId()
    {
        return $this->_attributes['id'];
    }

    public function getMaskedValue()
    {
        $maskItems = [];
        preg_match_all('/\{(.*?)\}/', static::$_entityMask, $maskItems);
        $maskedValue = $this->str(static::$_entityMask);
        foreach ($maskItems[1] as $attr) {
            $maskedValue->replace('{' . $attr . '}', $this->getAttribute($attr)->getValue());
        }

        return $maskedValue->val();
    }

    /**
     * Save entity attributes to database
     */
    public function save()
    {
        if (!$this->__getDirty() && !$this->isNull($this->getId()->getValue())) {
            return true;
        }

        $data = [];
        foreach ($this->getAttributes() as $key => $attr) {
            if (!$this->isInstanceOf($attr, AttributeType::ONE2MANY) && !$this->isInstanceOf($attr,
                                                                                             AttributeType::MANY2MANY)
            ) {
                $data[$key] = $attr->getDbValue();
            }
        }

        /**
         * Unset ID
         */
        unset($data['id']);

        /**
         * Insert or update
         */
        if ($this->getId()->getValue() === null) {
            Entity::getInstance()->getDatabase()->insert(static::$_entityCollection, $data);
            $this->getId()->setValue((string)$data['_id']);
        } else {
            $id = Entity::getInstance()->getDatabase()->id($this->getId()->getValue());
            Entity::getInstance()
                  ->getDatabase()
                  ->update(static::$_entityCollection, ['_id' => $id], ['$set' => $data], ['upsert' => true]);
        }

        $this->__setDirty(false);

        /**
         * Now save One2Many values
         */
        foreach ($this->getAttributes() as $attr) {
            /* @var $attr One2ManyAttribute */
            if ($this->isInstanceOf($attr, AttributeType::ONE2MANY)) {
                foreach ($attr->getValue() as $item) {
                    $item->getAttribute($attr->getRelatedAttribute())->setValue($this);
                    $item->save();
                }
                /**
                 * The value of one2many attribute must be set to null to trigger data reload on next access.
                 * This is necessary when we have circular references, and parent record does not get it's many2one ID saved
                 * until all child referenced objects are saved. Only then can we get proper links between referenced classes.
                 */
                $attr->setValue(null);
            }
        }

        /**
         * Now save Many2Many values
         */
        foreach ($this->getAttributes() as $attr) {
            /* @var $attr Many2ManyAttribute */
            if ($this->isInstanceOf($attr, AttributeType::MANY2MANY)) {
                Many2ManyStorage::getInstance()->save($attr);
            }
        }

        return true;
    }

    /**
     * Delete entity
     * @return bool
     * @throws EntityException
     */
    public function delete()
    {
        /**
         * Check for many2many attributes and make sure related Entity has a corresponding many2many attribute defined.
         * If not - deleting is not allowed.
         */

        /* @var $attr Many2ManyAttribute */
        $thisClass = '\\' . get_class($this);
        foreach ($this->getAttributes() as $attrName => $attr) {
            if ($this->isInstanceOf($attr, AttributeType::MANY2MANY)) {
                $foundMatch = false;
                $relatedClass = $attr->getEntity();
                $relatedEntity = new $relatedClass;
                /* @var $relAttr Many2ManyAttribute */
                foreach ($relatedEntity->getAttributes() as $relAttr) {
                    if ($this->isInstanceOf($relAttr, AttributeType::MANY2MANY) && $relAttr->getEntity() == $thisClass
                    ) {
                        $foundMatch = true;
                    }
                }

                if (!$foundMatch) {
                    throw new EntityException(EntityException::NO_MATCHING_MANY2MANY_ATTRIBUTE_FOUND, [
                                                                                                        $thisClass,
                                                                                                        $relatedClass,
                                                                                                        $attrName
                                                                                                    ]
                    );
                }
            }
        }

        /**
         * First check all one2many records to see if deletion is restricted
         */
        $deleteAttributes = [];
        foreach ($this->getAttributes() as $key => $attr) {
            if ($this->isInstanceOf($attr, AttributeType::ONE2MANY)) {
                /* @var $attr One2ManyAttribute */
                if ($attr->getOnDelete() == 'restrict' && $this->getAttribute($key)->getValue()->count() > 0) {
                    throw new EntityException(EntityException::ENTITY_DELETION_RESTRICTED, [$key]);
                }
                $deleteAttributes[] = $key;
            }
        }

        /**
         * Delete many2many records
         */
        foreach ($this->getAttributes() as $attr) {
            /* @var $attr Many2ManyAttribute */
            if ($this->isInstanceOf($attr, AttributeType::MANY2MANY)) {
                $firstClassName = $this->_extractClassName($attr->getParentEntity());
                $query = [$firstClassName => $this->getId()->getValue()];
                Entity::getInstance()->getDatabase()->remove($attr->getIntermediateCollection(), $query);
            }
        }

        /**
         * Delete one2many records
         */
        foreach ($deleteAttributes as $attr) {
            foreach ($this->getAttribute($attr)->getValue() as $item) {
                $item->delete();
            }
        }

        /**
         * Delete $this
         */
        Entity::getInstance()
              ->getDatabase()
              ->remove(static::$_entityCollection,
                       ['_id' => Entity::getInstance()->getDatabase()->id($this->getId()->getValue())]
              );

        EntityPool::getInstance()->remove($this);

        return true;
    }

    /**
     * Populate entity with given data
     *
     * @param array $data
     *
     * @throws EntityException
     * @return $this
     */
    public function populate($data)
    {
        $entityCollectionClass = '\Webiny\Component\Entity\EntityCollection';
        $validation = $this->arr();
        /* @var $entityAttribute AttributeAbstract */
        foreach ($this->_attributes as $attributeName => $entityAttribute) {
            // Check if attribute is required and it's value is set
            if ($entityAttribute->getRequired() && !isset($data[$attributeName])) {
                $validation[$attributeName] = new ValidationException(ValidationException::REQUIRED_ATTRIBUTE_IS_MISSING,
                                                                      [$attributeName]);
                continue;
            }

            // If 'required' check is passed, continue with other checks
            $canPopulate = !$this->getId()->getValue() || !$entityAttribute->getOnce();
            if (isset($data[$attributeName]) && $canPopulate) {
                $dataValue = $data[$attributeName];
                $isOne2Many = $this->isInstanceOf($entityAttribute, AttributeType::ONE2MANY);
                $isMany2Many = $this->isInstanceOf($entityAttribute, AttributeType::MANY2MANY);
                $isMany2One = $this->isInstanceOf($entityAttribute, AttributeType::MANY2ONE);

                if ($isMany2One) {
                    try {
                        $entityAttribute->validate($dataValue)->setValue($dataValue);
                    } catch (ValidationException $e) {
                        $validation[$attributeName] = $e;
                        continue;
                    }
                } elseif ($isOne2Many) {
                    $entityClass = $entityAttribute->getEntity();

                    // Validate One2Many attribute value
                    if (!$this->isArray($dataValue) && !$this->isArrayObject($dataValue) && !$this->isInstanceOf($dataValue,
                                                                                                                 $entityCollectionClass)
                    ) {
                        $validation[$attributeName] = new ValidationException(ValidationException::ATTRIBUTE_VALIDATION_FAILED,
                                                                              [
                                                                                  $attributeName,
                                                                                  'array, ArrayObject or EntityCollection',
                                                                                  gettype($dataValue)
                                                                              ]);
                        continue;
                    }
                    /* @var $entityAttribute One2ManyAttribute */
                    foreach ($dataValue as $item) {
                        $itemEntity = false;

                        // $item can be an array of data, EntityAbstract or a simple MongoId string
                        if ($this->isInstanceOf($item, '\Webiny\Component\Entity\EntityAbstract')) {
                            $itemEntity = $item;
                        } elseif ($this->isArray($item) || $this->isArrayObject($item)) {
                            $itemEntity = $entityClass::findById(isset($item['id']) ? $item['id'] : false);
                        } elseif ($this->isString($item) && Entity::getInstance()->getDatabase()->isMongoId($item)) {
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

                        // Add One2Many entity instance to current entity's attribute
                        $entityAttribute->add($itemEntity);
                    }
                } elseif ($isMany2Many) {
                    $entityAttribute->add($dataValue);
                } else {
                    try {
                        $entityAttribute->validate($dataValue)->setValue($dataValue);
                    } catch (ValidationException $e) {
                        $validation[$attributeName] = $e;
                    }
                }
            }
        }

        if ($validation->count() > 0) {
            $ex = new EntityException(EntityException::VALIDATION_FAILED, [$validation->count()]);
            $ex->setInvalidAttributes($validation);
            throw $ex;
        }

        return $this;
    }

    /**
     * This method allows us to use simplified accessor methods.
     * Ex: $person->company->name
     *
     * @param $name
     *
     * @return AttributeAbstract
     */
    public function __get($name)
    {
        return $this->getAttribute($name)->getValue();
    }

    /**
     * This method allows setting attribute values through simple assignment
     * Ex: $person->name = 'Webiny';
     *
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->getAttribute($name)->setValue($value);
    }


    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param mixed $offset <p>
     *                      An offset to check for.
     *                      </p>
     *
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset)
    {
        return isset($this->_attributes[$offset]);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param mixed $offset <p>
     *                      The offset to retrieve.
     *                      </p>
     *
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        return $this->_attributes[$offset];
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param mixed $offset <p>
     *                      The offset to assign the value to.
     *                      </p>
     * @param mixed $value  <p>
     *                      The value to set.
     *                      </p>
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->_attributes[$offset]->setValue($value);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param mixed $offset <p>
     *                      The offset to unset.
     *                      </p>
     *
     * @return void
     */
    public function offsetUnset($offset)
    {
        // Nothing to unset
    }

    /**
     * Parse order parameters and construct parameters suitable for MongoDB
     *
     * @param $order
     *
     * @return array
     */
    private static function _parseOrderParameters($order)
    {
        $parsedOrder = [];
        if (count($order) > 0) {
            foreach ($order as $o) {
                $o = self::str($o);
                if ($o->startsWith('-')) {
                    $parsedOrder[$o->subString(1, 0)->val()] = -1;
                } elseif ($o->startsWith('+')) {
                    $parsedOrder[$o->subString(1, 0)->val()] = 1;
                } else {
                    $parsedOrder[$o->val()] = 1;
                }
            }
        }

        return $parsedOrder;
    }

    /**
     * Extract short class name from class namespace or class instance
     *
     * @param string|EntityAbstract $class
     *
     * @return string
     */
    private function _extractClassName($class)
    {
        if (!$this->isString($class)) {
            $class = get_class($class);
        }

        return $this->str($class)->explode('\\')->last()->val();
    }
}