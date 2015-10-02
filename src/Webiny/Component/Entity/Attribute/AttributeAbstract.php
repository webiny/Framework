<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Entity\Attribute;

use JsonSerializable;
use Webiny\Component\Entity\EntityAbstract;
use Webiny\Component\Entity\EntityAttributeBuilder;
use Webiny\Component\StdLib\StdLibTrait;


/**
 * AttributeAbstract
 * @package Webiny\Component\Entity\AttributeType
 */
abstract class AttributeAbstract implements JsonSerializable
{
    use StdLibTrait;

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

    /**
     * @param string         $attribute
     * @param EntityAbstract $entity
     */
    public function __construct($attribute, EntityAbstract $entity)
    {
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
    public function getEntity(){
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
     * Get value that will be used when converting EntityAbstract to array
     * @return string
     */
    public function getToArrayValue()
    {
        return (string)$this;
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
     * @throws ValidationException
     * @return $this
     */
    public function validate(&$value)
    {
        return $this;
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
            unset($this->validators['required']);
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
}