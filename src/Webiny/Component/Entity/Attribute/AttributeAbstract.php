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
    protected $validators = [];
    protected $validationMessages = [];
    protected $storeToDb = true;
    protected $onValue = null;
    protected $onValueCallback = null;
    protected $onGetToArrayValue = null;
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

        return $value;
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
    public function getToArrayValue()
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
    public function setToArrayValue($callback)
    {
        $this->onGetToArrayValue = $callback;

        return $this;
    }

    public function getToArrayValueCallback()
    {
        return $this->onGetToArrayValue;
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

        return EntityAttributeBuilder::getInstance()->attr($attribute);
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
    public function getRequired()
    {
        return $this->required;
    }

    /**
     * Set 'once' flag<br>
     * If true, it tells EntityAbstract to only populate this attribute if it's a new entity<br>
     * This is useful when you want to protect values from being populate on secondary updates.
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

    public function on($value, $callable = null)
    {
        if (is_callable($value)) {
            $callable = $value;
            $value = null;
        }
        $this->onValue = $value;
        $this->onValueCallback = $callable;

        return $this;
    }

    /**
     * Set attribute value
     *
     * @param null $value
     *
     * @param bool $fromDb
     *
     * @return $this
     */
    public function setValue($value = null, $fromDb = false)
    {
        if (!$this->canAssign()) {
            return $this;
        }

        if (!$fromDb) {
            $this->validate($value);

            if ($this->onValueCallback !== null && ($this->onValue === null || $this->onValue === $value)) {
                $callable = $this->onValueCallback;
                if (is_string($this->onValueCallback)) {
                    $callable = [$this->entity, $this->onValueCallback];
                }
                $value = call_user_func_array($callable, [$value]);
            }
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
        if ($this->isNull($this->value) && !$this->isNull($this->defaultValue)) {
            return $this->defaultValue;
        }

        return $this->value;
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
     * @return array
     */
    public function getValidationMessages()
    {
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
        if ($this->entity->getId()->getValue() && $this->getOnce() && $this->value !== null) {
            return false;
        }

        return true;
    }

    /**
     * Take generated value and check if custom callback exists for this attribute.
     * If yes - return the processed value.
     *
     * @param $value
     *
     * @return mixed
     */
    protected function processToArrayValue($value)
    {
        if ($this->onGetToArrayValue !== null) {
            $callable = $this->onGetToArrayValue;
            if (is_string($this->onGetToArrayValue)) {
                $callable = [$this->entity, $this->onGetToArrayValue];
            }

            return call_user_func_array($callable, [$value]);
        }

        return $value;
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
}