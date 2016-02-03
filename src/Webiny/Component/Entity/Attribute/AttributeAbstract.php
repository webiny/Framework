<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Entity\Attribute;

use JsonSerializable;
use Webiny\Component\Entity\Attribute\Exception\ValidationException as AttributeValidationException;
use Webiny\Component\Entity\Entity;
use Webiny\Component\Entity\EntityAbstract;
use Webiny\Component\Entity\EntityAttributeBuilder;
use Webiny\Component\Entity\Validation\ValidationException;
use Webiny\Component\StdLib\FactoryLoaderTrait;
use Webiny\Component\StdLib\StdLibTrait;


/**
 * AttributeAbstract
 * @package Webiny\Component\Entity\AttributeType
 */
abstract class AttributeAbstract implements JsonSerializable
{
    use StdLibTrait, FactoryLoaderTrait;

    protected static $entityValidators;

    /**
     * @var EntityAbstract
     */

    protected $entity;
    protected $attribute = '';
    protected $defaultValue = null;
    protected $value = null;
    protected $required = false;
    protected $once = false;
    protected $skipOnPopulate = false;
    protected $validators = [];
    protected $validationMessages = [];
    protected $storeToDb = true;
    protected $setAfterPopulate = false;
    protected $onSetValue = null;
    protected $onSetCallback = null;
    protected $onGetCallback = null;
    protected $onToArrayCallback = null;
    protected $onToDbCallback = null;
    protected $validatorInterface = '\Webiny\Component\Entity\Validation\ValidatorInterface';

    /**
     * @param string         $attribute
     * @param EntityAbstract $entity
     */
    public function __construct($attribute, EntityAbstract $entity)
    {
        if (!self::$entityValidators) {
            self::$entityValidators = Entity::getConfig()->get('Validators', []);
        }
        $this->attribute = $attribute;
        $this->entity = $entity;
    }

    /**
     * Get attribute value as string
     *
     * @return string
     */
    public function __toString()
    {
        if ($this->isNull($this->value) && !$this->isNull($this->defaultValue)) {
            return (string)$this->defaultValue;
        }

        return $this->isNull($this->value) ? '' : (string)$this->value;
    }

    public function hasValue()
    {
        if ($this->value !== null) {
            return true;
        }

        return $this->defaultValue !== null;
    }

    /**
     * Get entity instance this attribute belongs to
     *
     * @return EntityAbstract
     */
    public function getEntity()
    {
        return $this->entity;
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

        return $this->processToDbValue($value);
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
     * Get value that will be used to represent this attribute when converting EntityAbstract to array
     *
     * @return string
     */
    public function toArray()
    {
        return $this->processToArrayValue((string)$this);
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

    public function hasToArrayCallback()
    {
        return $this->onToArrayCallback !== null;
    }

    /**
     * Create new attribute or get name of current attribute
     *
     * @param null|string $attribute
     *
     * @return EntityAttributeBuilder|string
     */
    public function attr($attribute = null)
    {
        if ($this->isNull($attribute)) {
            return $this->attribute;
        }

        return $this->entity->attr($attribute);
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
     * @return $this
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
     * If true, it tells EntityAbstract to only populate this attribute if it's a new entity<br>
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
     * This flag tells you whether this attribute should only be populated when it's a new EntityAbstract instance.<br>
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
        return $this->defaultValue;
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
     * @param null $value
     * @param bool $fromDb
     *
     * @return $this
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
            $this->validate($value);
        }

        $this->value = $value;

        return $this;
    }

    /**
     * Get attribute value
     *
     * @return $this
     */
    public function getValue()
    {
        $value = $this->value;
        if ($this->isNull($value) && !$this->isNull($this->defaultValue)) {
            $value = $this->defaultValue;
        }

        return $this->processGetValue($value);
    }

    /**
     * Perform validation against given value
     *
     * @param $value
     *
     * @return $this
     * @throws AttributeValidationException
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
        if ($this->entity->id && $this->getOnce() && $this->value !== null) {
            return false;
        }

        return true;
    }

    /**
     * Triggered when calling 'getValue()' on attribute instance
     *
     * @param $value
     *
     * @return mixed
     */
    protected function processGetValue($value)
    {
        return $this->processCallback($this->onGetCallback, $value);
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
     * @throws AttributeValidationException
     * @throws \Webiny\Component\StdLib\Exception\Exception
     */
    protected function applyValidator($validator, $key, $value, $messages = [])
    {
        try {
            if ($this->isString($validator)) {
                $params = $this->arr(explode(':', $validator));
                $vName = '';
                $validatorParams = [$value, $this, $params->removeFirst($vName)->val()];
                $validator = $this->factory(self::$entityValidators[$vName], $this->validatorInterface);
                call_user_func_array([$validator, 'validate'], $validatorParams);
            } elseif ($this->isCallable($validator)) {
                $vName = 'callable';
                $validator($value, $this);
            }
        } catch (ValidationException $e) {
            $msg = isset($messages[$vName]) ? $messages[$vName] : $e->getMessage();

            $ex = new AttributeValidationException(AttributeValidationException::VALIDATION_FAILED);
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
     * @return AttributeValidationException
     * @throws AttributeValidationException
     */
    protected function expected($expecting, $got, $return = false)
    {
        $ex = new AttributeValidationException(AttributeValidationException::VALIDATION_FAILED);
        $ex->addError($this->attribute, AttributeValidationException::DATA_TYPE, [
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
     * @param $callback
     * @param $value
     *
     * @return mixed
     */
    private function processCallback($callback, $value)
    {
        if ($callback) {
            if (is_string($callback)) {
                $callback = [$this->entity, $callback];
            }
            $value = call_user_func_array($callback, [$value]);
        }

        return $value;
    }
}