<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Entity\Attribute;

use JsonSerializable;
use Webiny\Component\Entity\Attribute\Validation\ValidationException;
use Webiny\Component\Entity\Entity;
use Webiny\Component\Entity\AbstractEntity;
use Webiny\Component\Entity\EntityAttributeContainer;
use Webiny\Component\StdLib\StdLibTrait;


/**
 * AbstractAttribute
 * @package Webiny\Component\Entity\AttributeType
 */
abstract class AbstractAttribute implements JsonSerializable
{
    use StdLibTrait;

    /**
     * Entity this attribute belongs to
     *
     * @var AbstractEntity
     */
    protected $parent;

    /**
     * Attribute name
     * @var null|string
     */
    protected $attribute = '';

    /**
     * Default value
     * @var null
     */
    protected $defaultValue = null;

    /**
     * Actual value
     * @var null
     */
    protected $value = null;

    /**
     * Is this attribute required
     * @var bool
     */
    protected $required = false;

    /**
     * If true - updating will be disabled
     * @var bool
     */
    protected $once = false;

    /**
     * If true - mass populate will skip this attribute
     * @var bool
     */
    protected $skipOnPopulate = false;

    /**
     * Attribute validators
     * @var array
     */
    protected $validators = [];

    /**
     * Validation messages
     * @var array
     */
    protected $validationMessages = [];

    /**
     * If true - will store this attribute to database
     * @var bool
     */
    protected $storeToDb = true;

    /**
     * If true - will wait until mass populate is finished and assign the value afterwards
     * @var bool
     */
    protected $setAfterPopulate = false;

    /**
     * Value to trigger $onSetCallback
     * @var null
     */
    protected $onSetValue = null;

    /**
     * Callback to execute before setting a new value.
     * The value returned from the callback will be set as a new attribute value.
     * @var null
     */
    protected $onSetCallback = null;

    /**
     * Callback to execute when attribute value is being accessed.
     * The value returned from the callback will be returned as attribute value.
     * @var null
     */
    protected $onGetCallback = null;

    /**
     * Callback to execute when toArray() is called on Entity.
     * The value returned from the callback will be returned as attribute value.
     * @var null
     */
    protected $onToArrayCallback = null;

    /**
     * If true - this attribute will be included in the output of entity's toArray method
     * @var null
     */
    protected $toArrayDefault = null;

    /**
     * Callback to execute when attribute is being stored to database.
     * The value returned from the callback will be returned as attribute value for database.
     * @var null
     */
    protected $onToDbCallback = null;

    /**
     * Callback to execute when attribute is being loaded from database.
     * The value returned from the callback will be set as attribute value
     * @var null
     */
    protected $onFromDbCallback = null;

    /**
     * @var string
     */
    protected $validatorInterface = '\Webiny\Component\Entity\Attribute\Validation\ValidatorInterface';

    /**
     * @param string         $name
     * @param AbstractEntity $parent
     */
    public function __construct($name = null, AbstractEntity $parent = null)
    {
        $this->attribute = $name;
        $this->parent = $parent;
    }

    /**
     * Get attribute value as string
     *
     * @return string
     */
    public function __toString()
    {
        $defaultValue = $this->getDefaultValue();
        if ($this->isNull($this->value) && !$this->isNull($defaultValue)) {
            return (string)$defaultValue;
        }

        return $this->isNull($this->value) ? '' : (string)$this->value;
    }

    /**
     * Get attribute name
     * @return string
     */
    public function getName()
    {
        return $this->attribute;
    }

    /**
     * Set attribute name
     *
     * @param string $attribute
     *
     * @return $this
     */
    public function setName($attribute)
    {
        $this->attribute = $attribute;

        return $this;
    }

    public function setParent(AbstractEntity $entity)
    {
        $this->parent = $entity;

        return $this;
    }

    public function hasValue()
    {
        if ($this->value !== null) {
            return true;
        }

        return false;
    }

    /**
     * Get entity instance this attribute belongs to
     *
     * @return AbstractEntity
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Get value that will be stored to database<br>
     *
     * If no value is set, default value will be used, and that value will also be assigned as a new attribute value.
     *
     * @return string
     */
    public function getDbValue()
    {
        $value = $this->getValue();
        if ($this->isNull($this->value)) {
            $this->value = $value;
        }

        $value = $this->processToDbValue($value);

        return $this->value = $value;
    }

    /**
     * Should this attribute value be stored to DB
     *
     * @return bool
     */
    public function getStoreToDb()
    {
        return $this->storeToDb;
    }

    /**
     * Get value that will be used to represent this attribute when converting AbstractEntity to array
     *
     * @param array $params
     *
     * @return mixed
     */
    public function toArray($params = [])
    {
        return $this->processToArrayValue($this->getValue($params));
    }

    /**
     * Set callback that will be used to process getToArrayValue() call
     *
     * @param $callback
     *
     * @return $this
     */
    public function onToArray($callback)
    {
        $this->onToArrayCallback = $callback;

        return $this;
    }

    /**
     * Get toArrayDefault flag
     *
     * @return bool
     */
    public function getToArrayDefault()
    {
        return $this->toArrayDefault;
    }

    /**
     * Set if this attribute will be by default included in the output of entity's toArray method
     *
     * @param boolean $flag
     *
     * @return $this
     */
    public function setToArrayDefault($flag = true)
    {
        $this->toArrayDefault = $flag;

        return $this;
    }

    /**
     * Set callback that will be used to process getDbValue() call
     *
     * @param $callback
     *
     * @return $this
     */
    public function onToDb($callback)
    {
        $this->onToDbCallback = $callback;

        return $this;
    }

    /**
     * Set callback that will be used to process value when data is loaded from DB
     *
     * @param $callback
     *
     * @return $this
     */
    public function onFromDb($callback)
    {
        $this->onFromDbCallback = $callback;

        return $this;
    }

    public function hasToArrayCallback()
    {
        return $this->onToArrayCallback !== null;
    }

    /**
     * Create new attribute or get name of current attribute
     *
     * @param null|string $attribute
     *
     * @return EntityAttributeContainer|string
     */
    public function attr($attribute = null)
    {
        if ($this->isNull($attribute)) {
            return $this->attribute;
        }

        return $this->parent->attr($attribute);
    }

    /**
     * Set required flag
     *
     * @param boolean $flag
     *
     * @return $this
     */
    public function setRequired($flag = true)
    {
        $this->required = $flag;

        return $this;
    }

    /**
     * Get required flag
     *
     * @return bool
     */
    public function isRequired()
    {
        return $this->required;
    }

    /**
     * Skip attribute on mass populate
     *
     * @param bool $flag
     *
     * @return $this
     */
    public function setSkipOnPopulate($flag = true)
    {
        $this->skipOnPopulate = $flag;

        return $this;
    }

    /**
     * Get skipOnPopulate flag
     *
     * @return bool
     */
    public function getSkipOnPopulate()
    {
        return $this->skipOnPopulate;
    }

    /**
     * Should this attribute's value be assigned after populate() ?
     * This is useful when onSet() callback is triggering business logic that depends on other attributes that are not yet set
     * because populate() cycle has not yet finished and onSet() callback of current attribute is executed
     *
     * @param bool $flag
     *
     * @return $this
     */
    public function setAfterPopulate($flag = true)
    {
        $this->setAfterPopulate = true;

        return $this;
    }

    /**
     * Should this attribute's value be assigned after populate() ?
     *
     * @return bool
     */
    public function getAfterPopulate()
    {
        return $this->setAfterPopulate;
    }

    /**
     * Set 'once' flag<br>
     * If true, it tells AbstractEntity to only populate this attribute if it's a new entity<br>
     * This is useful when you want to protect values from being populate on later updates.
     *
     * @param boolean $flag
     *
     * @return $this
     */
    public function setOnce($flag = true)
    {
        $this->once = $flag;

        return $this;
    }

    /**
     * Get 'once' flag<br>
     * This flag tells you whether this attribute should only be populated when it's a new AbstractEntity instance.<br>
     * By default, attributes are populated each time.
     *
     * @return $this
     */
    public function getOnce()
    {
        return $this->once;
    }


    /**
     * Set default value
     *
     * @param mixed $defaultValue
     *
     * @return $this
     */
    public function setDefaultValue($defaultValue = null)
    {
        $this->defaultValue = $defaultValue;

        return $this;
    }

    /**
     * Get default value
     *
     * @return mixed
     */
    public function getDefaultValue()
    {
        $defaultValue = $this->defaultValue;

        return is_callable($defaultValue) ? $defaultValue() : $defaultValue;
    }

    public function onSet($value, $callable = null)
    {
        if (is_callable($value) || (is_string($value) && $callable == null)) {
            $callable = $value;
            $value = null;
        }

        $this->onSetValue = $value;
        $this->onSetCallback = $callable;

        return $this;
    }

    public function onGet($callable = null)
    {
        $this->onGetCallback = $callable;

        return $this;
    }

    /**
     * Set attribute value
     *
     * @param null $value Attribute value
     * @param bool $fromDb Is value coming from DB?
     *
     * @return $this
     */
    public function setValue($value = null, $fromDb = false)
    {
        if ($fromDb) {
            $this->value = $this->processFromDbValue($value);

            return $this;
        }

        if (!$this->canAssign()) {
            return $this;
        }

        $value = $this->processSetValue($value);
        $this->validate($value);

        $this->value = $value;

        return $this;
    }

    /**
     * Get attribute value
     *
     * @param array $params
     * @param bool  $processCallbacks Process `onGet` callbacks
     *
     * @return $this
     */
    public function getValue($params = [], $processCallbacks = true)
    {
        if ($this->isNull($this->value) && !$this->isNull($this->defaultValue)) {
            $this->value = $this->getDefaultValue();
        }

        return $this->processGetValue($this->value, $params, $processCallbacks);
    }

    /**
     * Perform validation against given value
     *
     * @param $value
     *
     * @return $this
     * @throws ValidationException
     */
    protected function validate(&$value)
    {
        $validators = $this->getValidators();

        unset($validators['required']);

        // Do not validate if attribute value is not required and empty value is given
        // 'empty' function is not suitable for this check here
        if (!$this->required && (is_null($value) || $value === '')) {
            return;
        }

        $messages = $this->getValidationMessages();

        foreach ($validators as $validator) {
            $this->applyValidator($validator, $this->attribute, $value, $messages);
        }
    }

    /**
     * Set attribute validators
     *
     * @param array|string $validators
     *
     * @return $this
     */
    public function setValidators($validators = [])
    {
        if (is_array($validators)) {
            $this->validators = $validators;
        } else {
            $this->validators = func_get_args();
            if (count($this->validators) == 1 && is_string($this->validators[0])) {
                $this->validators = explode(',', $this->validators[0]);
            }
        }

        if (in_array('required', $this->validators)) {
            $this->setRequired();
            unset($this->validators[array_search('required', $this->validators)]);
        }


        return $this;

    }

    /**
     * Get validators
     * @return array
     */
    public function getValidators()
    {
        return $this->validators;
    }

    /**
     * Set validation messages
     *
     * @param array $messages
     *
     * @return $this
     */
    public function setValidationMessages($messages)
    {
        $this->validationMessages = $messages;

        return $this;
    }

    /**
     * Get validation messages
     *
     * @param string|null $validator If given, returns validation message for given validator
     *
     * @return mixed
     */
    public function getValidationMessages($validator = null)
    {
        if ($validator) {
            return isset($this->validationMessages[$validator]) ? $this->validationMessages[$validator] : null;
        }

        return $this->validationMessages;
    }

    /**
     * (PHP 5 &gt;= 5.4.0)<br/>
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     */
    public function jsonSerialize()
    {
        return $this->__toString();
    }

    /**
     * Check if value can be assigned to current attribute<br>
     *
     * If Entity has an ID and attribute 'once' flag is set and attribute has a value assigned to it
     * then a new value should not be assigned.
     *
     * @return bool
     */
    protected function canAssign()
    {
        if ($this->parent->id && $this->getOnce() && $this->value !== null) {
            return false;
        }

        return true;
    }

    /**
     * Triggered when calling 'getValue()' on attribute instance
     *
     * @param mixed $value
     * @param array $params
     * @param bool  $processCallbacks
     *
     * @return mixed
     */
    protected function processGetValue($value, $params = [], $processCallbacks = true)
    {
        return $processCallbacks ? $this->processCallback($this->onGetCallback, $value, $params) : $value;
    }

    /**
     * Triggered when calling 'setValue()' on attribute instance
     *
     * @param $value
     *
     * @return mixed
     */
    protected function processSetValue($value)
    {
        if ($this->onSetCallback !== null && ($this->onSetValue === null || $this->onSetValue === $value)) {
            return $this->processCallback($this->onSetCallback, $value);
        }

        return $value;
    }

    /**
     * Triggered when calling 'getDbValue()' on attribute instance
     *
     * @param $value
     *
     * @return mixed
     */
    protected function processToDbValue($value)
    {
        return $this->processCallback($this->onToDbCallback, $value);
    }

    /**
     * Triggered when calling 'setValue()' with $fromDb=true on attribute instance
     *
     * @param $value
     *
     * @return mixed
     */
    protected function processFromDbValue($value)
    {
        return $this->processCallback($this->onFromDbCallback, $value);
    }

    /**
     * Triggered when calling 'toArray()' on the entity instance
     *
     * @param $value
     *
     * @return mixed
     */
    protected function processToArrayValue($value)
    {
        return $this->processCallback($this->onToArrayCallback, $value);
    }

    /**
     * Apply validator to given value
     *
     * @param string $validator
     * @param string $key
     * @param mixed  $value
     * @param array  $messages
     *
     * @throws ValidationException
     * @throws \Webiny\Component\StdLib\Exception\Exception
     */
    protected function applyValidator($validator, $key, $value, $messages = [])
    {
        try {
            if ($this->isString($validator)) {
                $params = $this->arr(explode(':', $validator));
                $vName = '';
                $validatorParams = [$this, $value, $params->removeFirst($vName)->val()];
                $validator = Entity::getInstance()->getValidator($vName);
                if (!$validator) {
                    throw new ValidationException('Validator does not exist');
                }
                $validator->validate(...$validatorParams);
            } elseif ($this->isCallable($validator)) {
                $vName = 'callable';
                $validator($value, $this);
            }
        } catch (ValidationException $e) {
            $msg = isset($messages[$vName]) ? $messages[$vName] : $e->getMessage();

            $ex = new ValidationException($msg);
            $ex->addError($key, $msg);

            throw $ex;
        }
    }

    /**
     * Throw or return attribute validation exception
     *
     * @param string $expecting
     * @param string $got
     * @param bool   $return
     *
     * @return ValidationException
     * @throws ValidationException
     */
    protected function expected($expecting, $got, $return = false)
    {
        $ex = new ValidationException(ValidationException::VALIDATION_FAILED);
        $ex->addError($this->attribute, ValidationException::DATA_TYPE, [
            $expecting,
            $got
        ]);

        if ($return) {
            return $ex;
        }

        throw $ex;
    }

    /**
     * Execute given callback<br/>
     * Take $value and check if a valid callback is given<br/>
     * If yes, return the processed value.
     *
     * @param       $callback
     * @param       $value
     * @param array $params
     *
     * @return mixed
     */
    private function processCallback($callback, $value, $params = [])
    {
        if ($callback) {
            if (is_string($callback)) {
                $callback = [$this->parent, $callback];
            }

            array_unshift($params, $value);
            $value = call_user_func_array($callback, $params);
        }

        return $value;
    }
}