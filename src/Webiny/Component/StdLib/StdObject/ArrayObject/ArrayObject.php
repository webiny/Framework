<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @link      http://www.webiny.com/wf-snv for the canonical source repository
 * @copyright Copyright (c) 2009-2013 Webiny LTD. (http://www.webiny.com)
 * @license   http://www.webiny.com/framework/license
 * @package   WebinyFramework
 */

namespace Webiny\Component\StdLib\StdObject\ArrayObject;

use Traversable;
use Webiny\Component\StdLib\StdObject\StdObjectAbstract;
use Webiny\Component\StdLib\StdObject\StdObjectWrapper;
use Webiny\Component\StdLib\StdObject\ArrayObject\ManipulatorTrait;
use Webiny\Component\StdLib\StdObject\ArrayObject\ValidatorTrait;
use Webiny\Component\StdLib\StdObject\StringObject\StringObject;

/**
 * Array standard object.
 * This is a helper class for working with arrays.
 *
 * @package         Webiny\Component\StdLib\StdObject\ArrayObject
 */
class ArrayObject extends StdObjectAbstract implements \IteratorAggregate, \ArrayAccess, \Countable
{
    use ManipulatorTrait, ValidatorTrait;

    /**
     * @var array|null Current array.
     */
    protected $value;

    /**
     * Constructor.
     * Set standard object value.
     *
     * @param null|array|ArrayObject|stdClass $array  Array or stdClass from which to create an ArrayObject.
     * @param null|array                      $values Array of values that will be combined with $array.
     *                                                See http://www.php.net/manual/en/function.array-combine.php for more info.
     *                                                $array param is used as key array.
     *
     * @throws ArrayObjectException
     */
    public function __construct($array = null, $values = null)
    {
        if(!$this->isInstanceOf($array, '\stdClass') && !$this->isArray($array) && !$this->isArrayObject($array)) {
            if($this->isNull($array)) {
                $this->value = array();
            } else {
                throw new ArrayObjectException(ArrayObjectException::MSG_INVALID_PARAM, [
                                                                                          '$array',
                                                                                          'array, ArrayObject'
                                                                                      ]
                );
            }
        } else {
            $array = $this->objectToArray($array);
            if($this->isInstanceOf($array, $this)) {
                $this->val($array->val());
            } else {
                if($this->isArray($values)) {
                    // check if both arrays have the same number of values
                    if(count($array) != count($values)) {
                        throw new ArrayObjectException(ArrayObjectException::MSG_INVALID_COMBINE_COUNT);
                    }
                    $this->value = array_combine($array, $values);
                } else {
                    $this->value = $array;
                }
            }
        }
    }

    /**
     * Get the sum of all elements inside the array.
     *
     * @return number The sum of all elements from within the current array.
     */
    public function sum()
    {
        return array_sum($this->val());
    }

    /**
     * Get only the keys of current array.
     *
     * @return ArrayObject An ArrayObject containing only the keys of current array.
     */
    public function keys()
    {
        return new ArrayObject(array_keys($this->val()));
    }

    /**
     * Get only the values of current array.
     *
     * @return ArrayObject An ArrayObject containing only the values of current array.
     */
    public function values()
    {
        return new ArrayObject(array_values($this->val()));
    }

    /**
     * Get the last element in the array.
     * If the element is array, ArrayObject is returned, else StringObject is returned.
     *
     * @return StringObject|ArrayObject|StdObjectWrapper The last element in the array.
     */
    public function last()
    {
        $arr = $this->val();
        $last = end($arr);

        return StdObjectWrapper::returnStdObject($last);
    }

    /**
     * Get the first element in the array.
     * If the element is array, ArrayObject is returned, else StringObject is returned.
     *
     * @return StringObject|ArrayObject|StdObjectWrapper The first element in the array.
     */
    public function first()
    {
        $arr = $this->val();
        $first = reset($arr);

        return StdObjectWrapper::returnStdObject($first);
    }

    /**
     * Get the number of elements inside the array.
     *
     * @return int Number of elements inside the current array.
     */
    public function count()
    {
        return count($this->val());
    }

    /**
     * Counts the occurrences of the same array values and groups them into an associate array.
     * NOTE: This function can only count array values that are type of STRING of INTEGER.
     *
     * @throws ArrayObjectException
     * @return ArrayObject An ArrayObject containing the array values as keys and number of their occurrences as values.
     */
    public function countValues()
    {
        try {
            /**
             * We must mute errors in this function because it throws a E_WARNING message if array contains something
             * else than STRING or INTEGER.
             */
            @$result = array_count_values($this->val());

            return new ArrayObject($result);
        } catch (\ErrorException $e) {
            throw new ArrayObjectException(ArrayObjectException::MSG_INVALID_COUNT_VALUES);
        }

    }

    /**
     * To string implementation.
     *
     * @return mixed String 'Array'.
     */
    public function __toString()
    {
        return 'Array';
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
        return new \ArrayIterator($this->value);
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
     *       The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset)
    {
        return $this->keyExists($offset);
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
     * @param mixed $value  <p>
     *                      The value to set.
     *                      </p>
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        if($this->isNull($offset)){
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
     * Get key
     *
     * @param $name
     *
     * @return $this|mixed|StringObject
     */
    public function __get($name)
    {
        return $this->key($name);
    }

    /**
     * Set array object value
     *
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->key($name, $value);
    }

    private function objectToArray($object)
    {
        if($this->isInstanceOf($object, '\stdClass')) {
            // Gets the properties of the given object
            // with get_object_vars function
            $object = get_object_vars($object);
        }

        if(is_array($object)) {
            foreach ($object as $k => $v) {
                $object[$k] = $this->objectToArray($v);
            }

            return $object;
        } else {
            // Return array
            return $object;
        }
    }
}