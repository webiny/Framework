<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Entity\Attribute;

use Traversable;
use Webiny\Component\Entity\EntityAbstract;
use Webiny\Component\StdLib\StdObject\ArrayObject\ArrayObject;

/**
 * ArrayAttribute
 * @package Webiny\Component\Entity\AttributeType
 */
class ArrayAttribute extends AttributeAbstract implements \IteratorAggregate, \ArrayAccess
{
    public function setValue($value = null)
    {
        if($this->isNull($value)) {
            $value = [];
        }

        return parent::setValue($value);
    }


    /**
     * Perform validation against given value
     *
     * @param $value
     *
     * @throws ValidationException
     * @return $this
     */
    public function validate($value)
    {
        if($this->isNull($value)) {
            return $this;
        }

        if(!$this->isArray($value) && !$this->isArrayObject($value)) {
            throw new ValidationException(ValidationException::ATTRIBUTE_VALIDATION_FAILED, [
                    $this->_attribute,
                    'array or ArrayObject',
                    gettype($value)
                ]
            );
        }

        return $this;
    }

    public function getToArrayValue()
    {
        if($this->isNull($this->_value) && !$this->isNull($this->_defaultValue)) {
            return $this->_defaultValue;
        }

        return $this->_value;
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
        return $this->arr($this->_value)->keyNested($key, $default, true);
    }

    /**
     * Set value for given key (can be nested key, using dotted notation)
     *
     * @param string $key
     * @param mixed $value
     */
    public function set($key, $value)
    {
        $this->_value = $this->arr($this->_value)->keyNested($key, $value)->val();
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
        $this->_value = $this->arr($this->_value)->prepend($value)->val();

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
        $this->_value = $this->arr($this->_value)->append($value)->val();

        return $this;
    }

    function __set($name, $value)
    {
        die($name . ' - ' . $value);
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
        return new \ArrayIterator($this->_value->val());
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
        return isset($this->_value[$offset]);
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
        return $this->_value[$offset];
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
        if($this->isNull($offset)) {
            $this->_value[] = $value;
        } else {
            $this->_value[$offset] = $value;
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
        unset($this->_value[$offset]);
    }
}