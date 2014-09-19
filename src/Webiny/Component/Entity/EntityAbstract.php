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

        return EntityPool::getInstance()->add($instance->populate($data));
    }

    /**
     * Find entities
     *
     * @param mixed $conditions
     *
     * @param array $order
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
     * Get or set entity's dirty flag
     *
     * @param null $flag
     *
     * @return $this|bool
     */
    public function __dirty($flag = null)
    {
        if ($this->isNull($flag)) {
            return $this->_dirty;
        }
        $this->_dirty = boolval($flag);

        return $this;
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
        if (!$this->__dirty()) {
            return true;
        }
        $one2manyClass = '\Webiny\Component\Entity\Attribute\One2ManyAttribute';
        $many2manyClass = '\Webiny\Component\Entity\Attribute\Many2ManyAttribute';


        $data = [];
        foreach ($this->getAttributes() as $key => $attr) {
            if (!$this->isInstanceOf($attr, $one2manyClass) && !$this->isInstanceOf($attr, $many2manyClass)) {
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
        if ($this->getId()->getValue() == null) {
            $savedData = Entity::getInstance()->getDatabase()->insert(static::$_entityCollection, $data);
            $this->getId()->setValue((string)$savedData['_id']);
        } else {
            $id = Entity::getInstance()->getDatabase()->id($this->getId()->getValue());
            Entity::getInstance()
                  ->getDatabase()
                  ->update(static::$_entityCollection, ['_id' => $id], ['$set' => $data], ['upsert' => true]);
        }

        $this->__dirty(false);

        /**
         * Now save One2Many values
         */
        foreach ($this->getAttributes() as $attr) {
            /* @var $attr One2ManyAttribute */
            if ($this->isInstanceOf($attr, $one2manyClass)) {
                foreach ($attr->getValue() as $item) {
                    $item->{$attr->getRelatedAttribute()}->setValue($this);
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
            if ($this->isInstanceOf($attr, $many2manyClass)) {
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
        $one2manyClass = '\Webiny\Component\Entity\Attribute\One2ManyAttribute';
        $many2manyClass = '\Webiny\Component\Entity\Attribute\Many2ManyAttribute';


        /**
         * Check for many2many attributes and make sure related Entity has a corresponding many2many attribute defined.
         * If not - deleting is not allowed.
         */

        /* @var $attr Many2ManyAttribute */
        $thisClass = '\\' . get_class($this);
        foreach ($this->getAttributes() as $attrName => $attr) {
            if ($this->isInstanceOf($attr, $many2manyClass)) {
                $foundMatch = false;
                $relatedClass = $attr->getEntity();
                $relatedEntity = new $relatedClass;
                /* @var $relAttr Many2ManyAttribute */
                foreach ($relatedEntity->getAttributes() as $relAttr) {
                    if ($this->isInstanceOf($relAttr, $many2manyClass) && $relAttr->getEntity() == $thisClass) {
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
            if ($this->isInstanceOf($attr, $one2manyClass)) {
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
            if ($this->isInstanceOf($attr, $many2manyClass)) {
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
        $validation = $this->arr();
        /** @var $entityAttribute AttributeAbstract */
        foreach ($this->_attributes as $attributeName => $entityAttribute) {
            if (isset($data[$attributeName])) {
                $dataValue = $data[$attributeName];
                $isOne2Many = $this->isInstanceOf($entityAttribute, AttributeType::ONE2MANY);
                $isMany2Many = $this->isInstanceOf($entityAttribute, AttributeType::MANY2MANY);
                $isMany2One = $this->isInstanceOf($entityAttribute, AttributeType::MANY2ONE);

                if ($isMany2One) {
                    $entityAttribute->validate($dataValue)->setValue($dataValue);
                } elseif ($isOne2Many) {
                    $entityClass = $entityAttribute->getEntity();
                    /* @var $entityAttribute One2ManyAttribute */
                    foreach ($dataValue as $item) {
                        if (isset($item['id'])) {
                            $itemEntity = call_user_func_array([
                                                                   $entityClass,
                                                                   'findById'
                                                               ], [$item['id']]
                            );
                            if (!$itemEntity) {
                                $itemEntity = new $entityClass;
                            }
                        } else {
                            $itemEntity = new $entityClass;
                        }
                        $itemEntity->populate($item);
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
        return $this->getAttribute($name);
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
     * Extract short class name from class namespace
     *
     * @param $class
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