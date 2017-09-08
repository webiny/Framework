<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Entity;

use Webiny\Component\Entity\Attribute\AbstractAttribute;
use Webiny\Component\Entity\Attribute\AttributeType;
use Webiny\Component\Entity\Attribute\Validation\ValidationException;
use Webiny\Component\Entity\Attribute\Many2ManyAttribute;
use Webiny\Component\Entity\Attribute\One2ManyAttribute;
use Webiny\Component\StdLib\FactoryLoaderTrait;
use Webiny\Component\StdLib\StdLibTrait;
use Webiny\Component\StdLib\StdObject\StdObjectWrapper;

/**
 * Entity
 * @package \Webiny\Component\Entity
 */
abstract class AbstractEntity implements \ArrayAccess
{
    use StdLibTrait, EntityTrait, FactoryLoaderTrait;

    /**
     * This array serves as a log to prevent infinite save loop
     * @var array
     */
    private static $saved = [];

    /**
     * Entity attributes
     * @var EntityAttributeContainer
     */
    protected $attributes;

    /**
     * @var string Entity collection name
     */
    protected static $entityCollection = null;

    /**
     * View mask (used for generation of readable string when converting an instance to string)
     * @var string
     */
    protected static $entityMask = '{id}';

    /**
     * Get collection name
     * @return string
     */
    public static function getEntityCollection()
    {
        return static::$entityCollection;
    }

    /**
     * Find entity by ID
     *
     * @param $id
     *
     * @return null|AbstractEntity
     */
    public static function findById($id)
    {
        if (!$id || strlen($id) != 24) {
            return null;
        }
        $instance = static::entity()->get(get_called_class(), $id);
        if ($instance) {
            return $instance;
        }
        $mongo = static::entity()->getDatabase();
        $data = $mongo->findOne(static::$entityCollection, ['_id' => $mongo->id($id)]);
        if (!$data) {
            return null;
        }
        $instance = new static;
        $data['__webiny_db__'] = true;
        $instance->populate($data);

        return static::entity()->add($instance);
    }

    /**
     * Count records using given criteria
     *
     * @param array $conditions
     *
     * @return int
     *
     */
    public static function count(array $conditions = [])
    {
        return static::entity()->getDatabase()->count(static::$entityCollection, $conditions);
    }

    /**
     * Find entity by array of conditions
     *
     * @param array $conditions
     *
     * @return null|AbstractEntity
     * @throws EntityException
     */
    public static function findOne(array $conditions = [])
    {
        $data = static::entity()->getDatabase()->findOne(static::$entityCollection, $conditions);

        if (!$data) {
            return null;
        }

        $instance = new static;
        $data['__webiny_db__'] = true;
        $instance->populate($data);

        return static::entity()->add($instance);
    }

    /**
     * Find a random entity
     *
     * @param array $conditions
     *
     * @return null|AbstractEntity
     * @throws EntityException
     */
    public static function random(array $conditions = [])
    {
        $count = static::entity()->getDatabase()->count(static::$entityCollection, $conditions);
        if ($count === 0) {
            return null;
        }

        $data = static::find($conditions, [], 1, rand(0, $count));

        return $data[0];
    }

    /**
     * Finds one or more latest entities
     *
     * @param int $limit
     *
     * @return mixed|null
     */
    public static function latest($limit = 1)
    {
        $data = static::find([], ['_id' => -1], $limit);

        return $limit == 1 ? $data->first() : $data;
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
        $order = self::parseOrderParameters($order);
        $offset = $limit * ($page > 0 ? $page - 1 : 0);

        $data = self::entity()->getDatabase()->find(static::$entityCollection, $conditions, $order, $limit, $offset);
        $parameters = [
            'conditions' => $conditions,
            'order'      => $order,
            'limit'      => $limit,
            'offset'     => $offset
        ];

        return new EntityCollection(get_called_class(), $data, $parameters);
    }

    /**
     * Entity constructor
     */
    public function __construct()
    {
        /**
         * Add ID to the list of attributes
         */
        $this->attr('id')->char();
    }

    /**
     * @param string $attribute
     *
     * @return EntityAttributeContainer
     */
    public function attr($attribute)
    {
        if (!$this->attributes instanceof EntityAttributeContainer) {
            $this->attributes = new EntityAttributeContainer($this);
        }

        return $this->attributes->attr($attribute);
    }

    /**
     * Convert AbstractEntity to array with specified fields.
     * If no fields are specified, array will contain all simple and Many2One attributes
     *
     * @param string $fields List of fields to extract
     *
     * @return array
     */
    public function toArray($fields = '')
    {
        $dataExtractor = new EntityDataExtractor($this);

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
     * Is this entity already saved?
     *
     * @return bool
     */
    public function exists()
    {
        return $this->id !== null;
    }

    /**
     * Get entity attribute
     *
     * @param string $attribute
     *
     * @throws EntityException
     * @return AbstractAttribute
     */
    public function getAttribute($attribute)
    {
        if (!isset($this->attributes[$attribute])) {
            throw new EntityException(EntityException::ATTRIBUTE_NOT_FOUND, [
                $attribute,
                get_class($this)
            ]);
        }

        return $this->attributes[$attribute];
    }

    /**
     * Get all entity attributes
     *
     * @return EntityAttributeContainer
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    public function getMaskedValue()
    {
        $maskItems = [];
        preg_match_all('/\{(.*?)\}/', static::$entityMask, $maskItems);
        $maskedValue = $this->str(static::$entityMask);
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
        $objectHash = spl_object_hash($this);
        if (array_key_exists($objectHash, self::$saved)) {
            return true;
        }

        self::$saved[$objectHash] = true;

        $data = [];
        $validation = [];
        $one2many = AttributeType::ONE2MANY;
        $many2many = AttributeType::MANY2MANY;
        /* @var $attr AbstractAttribute */
        foreach ($this->getAttributes() as $key => $attr) {
            if ($attr->isRequired() && !$attr->hasValue()) {
                $ex = new ValidationException(ValidationException::VALIDATION_FAILED);
                $ex->addError($key, ValidationException::REQUIRED);
                $validation[$key] = $ex;
                continue;
            }

            if (!count($validation) && !$this->isInstanceOf($attr, $one2many) && !$this->isInstanceOf($attr, $many2many)) {
                if ($attr->getStoreToDb()) {
                    $data[$key] = $attr->getDbValue();
                }
            }
        }

        // Throw EntityException with invalid attributes
        if (count($validation)) {
            $attributes = [];
            foreach ($validation as $attr => $error) {
                foreach ($error as $key => $value) {
                    $attributes[$key] = $value;
                }
            }
            $ex = new EntityException(EntityException::VALIDATION_FAILED, [get_class($this), count($validation)]);
            $ex->setInvalidAttributes($attributes);
            throw $ex;
        }

        /**
         * Insert or update
         */
        $mongo = $this->entity()->getDatabase();
        if (!$this->exists()) {
            $data['_id'] = $mongo->isId($data['id']) ? $mongo->id($data['id']) : $mongo->id();
            $data['id'] = (string)$data['_id'];
            $mongo->insertOne(static::$entityCollection, $data);
            $this->id = $data['id'];
        } else {
            $where = ['_id' => $mongo->id($this->id)];
            $mongo->update(static::$entityCollection, $where, ['$set' => $data], ['upsert' => true]);
        }

        /**
         * Now save One2Many values
         */
        foreach ($this->getAttributes() as $attr) {
            /* @var $attr One2ManyAttribute */
            if ($this->isInstanceOf($attr, AttributeType::ONE2MANY) && $attr->isLoaded()) {
                foreach ($attr->getValue() as $item) {
                    $item->getAttribute($attr->getRelatedAttribute())->setValue($this);
                    $item->save();
                }
                /**
                 * The value of one2many attribute must be set to null to trigger data reload on next access.
                 * This is necessary when we have circular references, and parent record does not get it's many2one ID saved
                 * until all child referenced objects are saved. Only then can we get proper links between referenced classes.
                 */
                $attr->setValue(null, true);
            }
        }

        /**
         * Now save Many2Many values
         */
        foreach ($this->getAttributes() as $attr) {
            /* @var $attr Many2ManyAttribute */
            if ($this->isInstanceOf($attr, AttributeType::MANY2MANY)) {
                $attr->save();
            }
        }

        // Now that this entity is saved, remove it from save log
        unset(self::$saved[$objectHash]);

        return true;
    }

    /**
     * Delete entity
     * @return bool
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
        $thisClass = get_class($this);
        foreach ($this->getAttributes() as $attrName => $attr) {
            if ($this->isInstanceOf($attr, AttributeType::MANY2MANY)) {
                $foundMatch = false;
                $relatedClass = $attr->getEntity();
                $relatedEntity = new $relatedClass;
                /* @var $relAttr Many2ManyAttribute */
                foreach ($relatedEntity->getAttributes() as $relAttr) {
                    if ($this->isInstanceOf($relAttr, AttributeType::MANY2MANY) && $this->isInstanceOf($this, $relAttr->getEntity())) {
                        $foundMatch = true;
                    }
                }

                if (!$foundMatch) {
                    throw new EntityException(EntityException::NO_MATCHING_MANY2MANY_ATTRIBUTE_FOUND, [
                        $thisClass,
                        $relatedClass,
                        $attrName
                    ]);
                }
            }
        }

        /**
         * First check all one2many records to see if deletion is restricted
         */
        $one2manyDelete = [];
        $many2oneDelete = [];
        $many2manyDelete = [];
        foreach ($this->getAttributes() as $key => $attr) {
            if ($this->isInstanceOf($attr, AttributeType::ONE2MANY)) {
                if ($attr->getOnDelete() == 'ignore') {
                    continue;
                }

                /* @var $attr One2ManyAttribute */
                if ($attr->getOnDelete() == 'restrict' && $this->getAttribute($key)->getValue()->count() > 0) {
                    throw new EntityException(EntityException::ENTITY_DELETION_RESTRICTED, [$key]);
                }
                $one2manyDelete[] = $attr;
            }

            if ($this->isInstanceOf($attr, AttributeType::MANY2ONE)) {
                /* @var $attr Many2OneAttribute */
                if ($attr->getOnDelete() === 'cascade') {
                    $many2oneDelete[] = $attr;
                }
                continue;
            }

            if ($this->isInstanceOf($attr, AttributeType::MANY2MANY)) {
                $many2manyDelete[] = $attr;
            }
        }

        /**
         * Delete one2many records
         */
        foreach ($one2manyDelete as $attr) {
            foreach ($attr->getValue() as $item) {
                $item->delete();
            }
        }

        /**
         * Delete many2many records
         */
        foreach ($many2manyDelete as $attr) {
            $attr->unlinkAll();
        }

        /**
         * Delete many2one records that are set to 'cascade'
         */
        foreach ($many2oneDelete as $attr) {
            $value = $attr->getValue();
            if ($value && $value instanceof AbstractEntity) {
                $value->delete();
            }
        }

        /**
         * Delete $this
         */
        $this->entity()->getDatabase()->delete(static::$entityCollection, ['_id' => $this->entity()->getDatabase()->id($this->id)]);

        static::entity()->remove($this);

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
        if (is_null($data)) {
            return $this;
        }

        $data = $this->normalizeData($data);

        $fromDb = false;
        if ($this->isDbData($data)) {
            $fromDb = true;
        } else {
            unset($data['id']);
            unset($data['_id']);
        }

        $validation = $this->arr();

        /* @var $entityAttribute AbstractAttribute */
        foreach ($this->attributes as $attributeName => $entityAttribute) {
            if (!$entityAttribute->getAfterPopulate()) {
                $this->populateAttribute($attributeName, $entityAttribute, $validation, $data, $fromDb);
            }
        }

        foreach ($this->attributes as $attributeName => $entityAttribute) {
            if ($entityAttribute->getAfterPopulate()) {
                $this->populateAttribute($attributeName, $entityAttribute, $validation, $data, $fromDb);
            }
        }

        if ($validation->count() > 0) {
            $attributes = [];
            foreach ($validation as $attr => $error) {
                foreach ($error as $key => $value) {
                    $attributes[$key] = $value;
                }
            }
            $ex = new EntityException(EntityException::VALIDATION_FAILED, [get_class($this), $validation->count()]);
            $ex->setInvalidAttributes($attributes);
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
     * @return AbstractAttribute
     */
    public function __get($name)
    {
        return $this->getAttribute($name)->getValue();
    }

    function __call($name, $arguments)
    {
        $attr = $this->getAttribute($name);

        return $attr->getValue($arguments);
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
     * @inheritdoc
     */
    public function offsetExists($offset)
    {
        return isset($this->attributes[$offset]);
    }

    /**
     * @inheritdoc
     */
    public function offsetGet($offset)
    {
        return $this->__get($offset);
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value)
    {
        $this->__set($offset, $value);
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset)
    {
        // Nothing to unset
    }

    /**
     * Used for checking if the entity populate data is coming from database
     *
     * @param $data
     *
     * @return bool
     */
    protected function isDbData($data)
    {
        return isset($data['__webiny_db__']) && $data['__webiny_db__'];
    }

    /**
     * Parse order parameters and construct parameters suitable for MongoDB
     *
     * @param $order
     *
     * @return array
     */
    protected static function parseOrderParameters($order)
    {
        $parsedOrder = [];
        if (count($order) > 0) {
            foreach ($order as $key => $o) {
                // Check if $order array is already formatted properly
                if (!is_numeric($key) && is_numeric($o)) {
                    $parsedOrder[$key] = $o;
                    continue;
                }
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

    private function populateAttribute($attributeName, AbstractAttribute $entityAttribute, $validation, $data, $fromDb)
    {
        // Skip population of protected attributes if data is not coming from DB
        if (!$fromDb && $entityAttribute->getSkipOnPopulate()) {
            return;
        }

        // Dynamic attributes from database should be populated without any checks, and skipped otherwise
        if ($this->isInstanceOf($entityAttribute, AttributeType::DYNAMIC) && isset($data[$attributeName])) {
            $entityAttribute->setValue($data[$attributeName], $fromDb);

            return;
        }

        /**
         * Check if attribute is required and it's value is set or maybe value was already assigned
         */
        if (!$fromDb && $entityAttribute->isRequired() && !isset($data[$attributeName]) && !$entityAttribute->hasValue()) {
            $message = $entityAttribute->getValidationMessages('required');
            if (!$message) {
                $message = ValidationException::REQUIRED;
            }
            $ex = new ValidationException(ValidationException::VALIDATION_FAILED);
            $ex->addError($attributeName, $message, []);
            $validation[$attributeName] = $ex;

            return;
        }

        /**
         * In case it is an update - if the attribute is not in new $data, it's no big deal, we already have the previous value.
         */
        $dataIsSet = array_key_exists($attributeName, $data);
        if (!$dataIsSet && $this->exists()) {
            return;
        }

        $canPopulate = !$this->exists() || $fromDb || !$entityAttribute->getOnce();
        if ($dataIsSet && $canPopulate) {
            $dataValue = $data[$attributeName];

            try {
                $entityAttribute->setValue($dataValue, $fromDb);
            } catch (ValidationException $e) {
                $validation[$attributeName] = $e;
            }
        }
    }

    private function normalizeData($data)
    {
        if ($this->isArray($data) || $this->isArrayObject($data)) {
            return StdObjectWrapper::toArray($data);
        }


        return $data;
    }
}