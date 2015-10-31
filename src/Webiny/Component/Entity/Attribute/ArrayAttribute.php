<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Entity\Attribute;

use Traversable;
use Webiny\Component\Entity\EntityAbstract;
use Webiny\Component\Entity\Validation\ValidationException;
use Webiny\Component\Entity\Attribute\Exception\ValidationException as AttributeValidationException;
use Webiny\Component\StdLib\StdObject\ArrayObject\ArrayObject;

/**
 * ArrayAttribute
 * @package Webiny\Component\Entity\AttributeType
 */
class ArrayAttribute extends AttributeAbstract implements \IteratorAggregate, \ArrayAccess
{
    protected $keyValidators = [];
    protected $keyValidationMessages = [];

    public function __construct($attribute, EntityAbstract $entity)
    {
        parent::__construct($attribute, $entity);
        $this->value = new ArrayObject();
        $this->defaultValue = new ArrayObject();
    }

    public function getDbValue()
    {
        if ($this->value->count() == 0) {
            $value = $this->isStdObject($this->defaultValue) ? $this->defaultValue->val() : $this->defaultValue;
        } else {
            $value = $this->value->val();
        }

        return $value;
    }

    public function setValue($value = null, $fromDb = false)
    {
        if ($this->isNull($value)) {
            $value = new ArrayObject();
        }

        $value = $this->arr($value);

        return parent::setValue($value, $fromDb);
    }

    /**
     * @inheritDoc
     */
    public function getValue()
    {
        $defaultValue = new ArrayObject($this->defaultValue);
        if ($this->value->count() == 0 && $defaultValue->count() > 0) {
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
        if ($this->isNull($value)) {
            return $this;
        }

        if (!$this->isArray($value) && !$this->isArrayObject($value)) {
            $this->expected('array or ArrayObject', gettype($value));
        }

        // Call parent method
        parent::validate($value);

        // Validate array keys
        $this->validateNestedKeys($value);

        return $this;
    }

    public function getToArrayValue()
    {
        if ($this->value->count() == 0) {
            $value = $this->isStdObject($this->defaultValue) ? $this->defaultValue->val() : $this->defaultValue;

            return $this->processToArrayValue($value);
        }

        return $this->processToArrayValue($this->value->val());
    }


    /**
     * Get value or return $default if there is no element set.
     * You can also access deeper values by using dotted key notation: level1.level2.level3.key
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed Array value or default value
     */
    public function get($key, $default = null)
    {
        return $this->value->keyNested($key, $default, true);
    }

    /**
     * Set value for given key (can be nested key, using dotted notation)
     *
     * @param string $key
     * @param mixed  $value
     */
    public function set($key, $value)
    {
        $this->value->keyNested($key, $value);
    }

    /**
     * Prepend value to array
     *
     * @param mixed $value
     *
     * @return $this
     */
    public function prepend($value)
    {
        $this->value->prepend($value);

        return $this;
    }

    /**
     * Append value to array
     *
     * @param mixed $value
     *
     * @return $this
     */
    public function append($value)
    {
        $this->value->append($value);

        return $this;
    }

    /**
     * Set key validators
     *
     * @param array $validators
     *
     * @return $this
     */
    public function setKeyValidators(array $validators)
    {
        $this->keyValidators = $validators;

        return $this;
    }

    /**
     * Get key validators
     * @return mixed
     */
    public function getKeyValidators()
    {
        return $this->keyValidators;
    }

    /**
     * Set key validation messages
     *
     * @param $messages
     *
     * @return $this
     */
    public function setKeyValidationMessages($messages)
    {
        $this->keyValidationMessages = $messages;

        return $this;
    }

    /**
     * Get key validation messages
     *
     * @return array
     */
    public function getKeyValidationMessages()
    {
        return $this->keyValidationMessages;
    }

    public function _set($name, $value)
    {
        $this->offsetSet($name, $value);
    }


    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->value->val());
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
        return isset($this->value[$offset]);
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
        return $this->value[$offset];
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param mixed $offset <p>
     *                      The offset to assign the value to.
     *                      </p>
     * @param mixed $value <p>
     *                      The value to set.
     *                      </p>
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        if ($this->isNull($offset)) {
            $this->value[] = $value;
        } else {
            $this->value[$offset] = $value;
        }
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
        unset($this->value[$offset]);
    }

    /**
     * @param mixed $data
     *
     * @throws Exception\ValidationException
     */
    private function validateNestedKeys($data)
    {
        $ex = new AttributeValidationException(AttributeValidationException::VALIDATION_FAILED, [$this->attribute]);
        $messages = $this->getKeyValidationMessages();
        $keyValidators = $this->getKeyValidators();
        $errors = [];

        foreach ($keyValidators as $key => $validators) {
            $keyValue = $this->arr($data)->keyNested($key);
            if ($this->isString($validators)) {
                $validators = explode(',', $validators);
            }

            // Do not validate if attribute value is not required and empty value is given
            // 'empty' function is not suitable for this check here
            if (!in_array('required', $validators) && (is_null($keyValue) || $keyValue === '')) {
                continue;
            }

            foreach ($validators as $validator) {
                try {
                    $this->applyValidator($validator, $key, $keyValue, isset($messages[$key]) ? $messages[$key] : []);
                } catch (AttributeValidationException $e) {
                    foreach ($e as $key => $error) {
                        $errors[$this->attr() . '.' . $key] = $error;
                    }
                }
            }
        }

        if (count($errors)) {
            foreach ($errors as $key => $msg) {
                $ex->addError($key, $msg);
            }
            throw $ex;
        }
    }
}