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

use Webiny\Component\StdLib\StdObject\StdObjectManipulatorTrait;
use Webiny\Component\StdLib\StdObject\StdObjectWrapper;
use Webiny\Component\StdLib\StdObject\StringObject\StringObject;

/**
 * ArrayObject manipulator methods.
 *
 * @package         Webiny\Component\StdLib\StdObject\ArrayObject
 */
trait ManipulatorTrait
{
    /**
     * Get or update the given key inside current array.
     *
     * @param string|int|StringObject $key                  Array key
     * @param null|mixed              $value                If set, the value under current $key will be updated and not returned.
     * @param bool                    $setOnlyIfDoesntExist Set the $value only in case if the $key doesn't exist.
     *
     * @return $this|mixed|StringObject The value of the given key.
     */
    public function key($key, $value = null, $setOnlyIfDoesntExist = false)
    {
        $key = StdObjectWrapper::toString($key);
        $array = $this->val();

        if ($setOnlyIfDoesntExist && !$this->keyExists($key)) {
            $array[$key] = $value;
            $this->val($array);

            return $value;
        } else {
            if (!$setOnlyIfDoesntExist) {
                if (!$this->isNull($value)) {
                    $array[$key] = $value;
                    $this->val($array);

                    return $this;
                }
            }
        }

        return $array[$key];
    }

    /**
     * Get or update the given key inside current array. This method supports nested key access: 'level1.level2.level3'
     *
     * @param string|int|StringObject $key                  Array key Eg: 'level1.level2.level3'
     * @param null|mixed              $value                If set, the value under current $key will be updated and not returned.
     * @param bool                    $setOnlyIfDoesntExist Set the $value only in case if the $key doesn't exist.
     *
     * @return $this|mixed|StringObject The value of the given key.
     */
    public function keyNested($key, $value = null, $setOnlyIfDoesntExist = false)
    {
        $key = StdObjectWrapper::toString($key);
        $array = $this->val();

        if ($setOnlyIfDoesntExist && !$this->keyExistsNested($key)) {
            if (strpos($key, '.') !== false) {
                $keys = explode('.', trim($key, '.'), 2);
                if (!isset($array[$keys[0]])) {
                    $array[$keys[0]] = [];
                }
                $targetArray = new ArrayObject($array[$keys[0]]);
                $value = $targetArray->keyNested($keys[1], $value, true);
                $this->keyNested($keys[0], $targetArray->val());
            } else {
                $array[$key] = $value;
                $this->val($array);
            }

            return $value;
        } else {
            if (!$setOnlyIfDoesntExist && !$this->isNull($value)) {
                if (strpos($key, '.') !== false) {
                    $keys = explode('.', trim($key, '.'), 2);
                    if (!isset($array[$keys[0]])) {
                        $array[$keys[0]] = [];
                    }
                    $targetArray = new ArrayObject($array[$keys[0]]);
                    $targetArray->keyNested($keys[1], $value);
                    $this->keyNested($keys[0], $targetArray->val());
                } else {
                    $array[$key] = $value;
                    $this->val($array);
                }

                return $this;
            }
        }

        if (strpos($key, '.') !== false) {
            $keys = explode('.', trim($key, '.'), 2);
            $targetArray = new ArrayObject($array[$keys[0]]);

            return $targetArray->keyNested($keys[1], $value, true);
        }

        return $array[$key];
    }

    /**
     * Inserts an element to the end of the array.
     * If you set both params, that first param is the key, and second is the value,
     * else first param is the value, and the second is ignored.
     *
     * @param mixed $k
     * @param mixed $v
     *
     * @return $this
     */
    public function append($k, $v = null)
    {
        $array = $this->val();

        if (!$this->isNull($v)) {
            $array[$k] = $v;
        } else {
            $array[] = $k;
        }

        $this->val($array);

        return $this;
    }

    /**
     * Inserts an element at the beginning of the array.
     * If you set both params, that first param is the key, and second is the value,
     * else first param is the value, and the second is ignored.
     *
     * @param mixed $k
     * @param mixed $v
     *
     * @return $this
     */
    public function prepend($k, $v = null)
    {
        $array = $this->val();

        if (!$this->isNull($v)) {
            $array = array_reverse($array, true);
            $array[$k] = $v;
            $array = array_reverse($array, true);
        } else {
            array_unshift($array, $k);
        }

        $this->val($array);

        return $this;
    }

    /**
     * Removes the first element from the array.
     * If $assign parameter is given, the removed value will be assigned to it.
     *
     * @param null $assign Assign removed value to given parameter
     *
     * @return $this
     */
    public function removeFirst(&$assign = null)
    {
        $array = $this->val();

        if (count(func_get_args()) > 0) {
            $assign = $this->first()->val();
        }
        array_shift($array);

        $this->val($array);

        return $this;
    }

    /**
     * Removes the last element from the array.
     *
     * @param null $assign Assign removed value to given parameter
     *
     * @return $this
     */
    public function removeLast(&$assign = null)
    {
        $array = $this->val();

        if (count(func_get_args()) > 0) {
            $assign = $this->last()->val();
        }
        array_pop($array);

        $this->val($array);

        return $this;
    }

    /**
     * Remove the element(s) from the array under the given $key.
     *
     * @param string|array $key Key(s) that will be removed from the array.
     *
     * @return $this
     */
    public function removeKey($key)
    {
        if (is_array($key)) {
            foreach ($key as $k) {
                $this->removeKey($k);
            }

            return $this;
        }

        if ($this->keyExists($key)) {
            $array = $this->val();
            unset($array[$key]);

            $this->val($array);
        }

        return $this;
    }

    /**
     * Implode the array with the given $glue.
     *
     * @param string $glue String that will be used to put elements together.
     *
     * @return StringObject Instance of StringObject containing the array elements glued together as a string.
     */
    public function implode($glue)
    {
        $array = $this->val();
        $string = implode($glue, $array);

        return new StringObject($string);
    }

    /**
     * Split an array into chunks.
     *
     * @param int  $size          Chunk size.
     * @param bool $preserve_keys Do you want ot preserve keys.
     *
     * @return ArrayObject  A new instance of ArrayObject containing a multidimensional numerically indexed array,
     *                        starting with zero, with each dimension containing size elements.
     * @throws ArrayObjectException
     */
    public function chunk($size, $preserve_keys = false)
    {
        try {
            $chunk = array_chunk($this->val(), $size, $preserve_keys);
        } catch (\ErrorException $e) {
            throw new ArrayObjectException($e->getMessage());
        }

        return new ArrayObject($chunk);
    }

    /**
     * Change the case of all keys in current ArrayObject.
     *
     * @param string $case Case to which you want to covert array keys. Can be 'lower' or 'upper'.
     *
     * @return $this
     * @throws ArrayObjectException
     */
    public function changeKeyCase($case)
    {
        // validate case
        $case = new StringObject($case);
        $case->caseLower();
        $realCase = '';
        if ($case->equals('lower')) {
            $realCase = CASE_LOWER;
        } else {
            if ($case->equals('upper')) {
                $realCase = CASE_UPPER;
            } else {
                throw new ArrayObjectException(ArrayObjectException::MSG_PARAM_VALUE_OUT_OF_SCOPE, [
                                                                                                     '$case',
                                                                                                     '"lower" or "upper"'
                                                                                                 ]
                );
            }
        }

        $this->val(array_change_key_case($this->val(), $realCase));

        return $this;
    }

    /**
     * Use current array as keys and fill them with $value.
     * @link http://php.net/manual/en/function.array-fill-keys.php
     *
     * @param mixed $value Value to use for filling.
     *
     * @throws ArrayObjectException
     * @return $this
     */
    public function fillKeys($value)
    {
        try {
            $arr = array_fill_keys($this->val(), $value);
        } catch (\ErrorException $e) {
            throw new ArrayObjectException($e->getMessage());
        }


        $this->val($arr);

        return $this;
    }

    /**
     * Fill array with values.
     * Number of items is defined by $num param, and start index is defined by $start param.
     * If you have items in your current array, they will be removed.
     *
     * @param int   $start The first index of the returned array.
     * @param int   $num   Number of elements to insert. Must be greater than zero.
     * @param mixed $value Value to use for filling.
     *
     * @return $this
     * @throws ArrayObjectException
     */
    public function fill($start, $num, $value)
    {
        if (!$this->isNumber($start)) {
            throw new ArrayObjectException(ArrayObjectException::MSG_INVALID_PARAM, [
                                                                                      '$start',
                                                                                      'integer'
                                                                                  ]
            );
        }

        if (!$this->isNumber($num)) {
            throw new ArrayObjectException(ArrayObjectException::MSG_INVALID_PARAM, [
                                                                                      '$num',
                                                                                      'integer'
                                                                                  ]
            );
        }

        if ($num <= 0) {
            throw new ArrayObjectException(ArrayObjectException::MSG_PARAM_VALUE_OUT_OF_SCOPE, [
                                                                                                 '$num',
                                                                                                 'greater than zero'
                                                                                             ]
            );
        }

        $this->val(array_fill($start, $num, $value));

        return $this;
    }

    /**
     * Filter array values by using a callback function.
     *
     * @param string $callable
     *
     * @return $this
     * @throws ArrayObjectException
     */
    public function filter($callable = '')
    {
        if ($callable != '' && !$this->isCallable($callable)) {
            throw new ArrayObjectException(ArrayObjectException::MSG_INVALID_PARAM, [
                                                                                      '$callable',
                                                                                      'a callable function or method'
                                                                                  ]
            );
        }

        if ($callable == '') {
            // Filter all values equal to FALSE
            $this->val(array_filter($this->val()));
        } else {
            // Filter values using a callback
            $this->val(array_filter($this->val(), $callable));
        }

        return $this;
    }

    /**
     * Exchanges all keys with their associated values in the array.
     * Note: This function can only flip STRING and INTEGER values. Other types will throw a E_WARNING
     * (no exceptions, sorry :( )
     *
     * @throws ArrayObjectException
     * @return $this
     */
    public function flip()
    {
        try {
            $this->val(array_flip($this->val()));
        } catch (\ErrorException $e) {
            throw new ArrayObjectException($e->getMessage());
        }

        return $this;
    }

    /**
     * Remove all elements from the current array that are not present in the given $array.
     * Keys ARE NOT used in the comparison.
     *
     * @param $array Array for comparison.
     *
     * @return $this
     */
    public function intersect($array)
    {
        $this->val(array_intersect($this->val(), $array));

        return $this;
    }

    /**
     * Remove all elements from the current array that are not present in the given $array.
     * This function uses array keys for comparison unlike 'intersect' method.
     *
     * @param array  $array    Array for comparison
     * @param string $callable Optional callback function that can be uses for comparison.
     *
     * @return $this
     * @throws ArrayObjectException
     */
    public function intersectAssoc($array, $callable = '')
    {
        if ($callable != '') {
            if (!$this->isCallable($callable)) {
                throw new ArrayObjectException(ArrayObjectException::MSG_INVALID_PARAM, [
                                                                                          '$callable',
                                                                                          'a callable function or method'
                                                                                      ]
                );
            }

            $this->val(array_intersect_uassoc($this->val(), $array, $callable));
        } else {
            $this->val(array_intersect_assoc($this->val(), $array));
        }

        return $this;
    }

    /**
     * Remove all elements from the current array that are not present in the given $array.
     * This function compares ONLY array keys.
     *
     * @param array  $array    Array for comparison
     * @param string $callable Optional callback function that can be uses for comparison.
     *
     * @return $this
     * @throws ArrayObjectException
     */
    public function intersectKey($array, $callable = '')
    {
        if ($callable != '') {
            if (!$this->isCallable($callable)) {
                throw new ArrayObjectException(ArrayObjectException::MSG_INVALID_PARAM, [
                                                                                          '$callable',
                                                                                          'a callable function or method'
                                                                                      ]
                );
            }

            $this->val(array_intersect_ukey($this->val(), $array, $callable));
        } else {
            $this->val(array_intersect_key($this->val(), $array));
        }

        return $this;
    }

    /**
     * Apply the $callback function to all elements of current array.
     *
     * @param string $callable A callable function.
     *
     * @return $this
     * @throws ArrayObjectException
     */
    public function map($callable)
    {
        if (!$this->isCallable($callable)) {
            throw new ArrayObjectException(ArrayObjectException::MSG_INVALID_PARAM, [
                                                                                      '$callable',
                                                                                      'a callable function or method'
                                                                                  ]
            );
        }

        $this->val(array_map($callable, $this->val()));

        return $this;
    }

    /**
     * Merge given $array with current array.
     *
     * @param array|ArrayObject $array
     *
     * @return $this
     */
    public function merge($array)
    {
        if ($this->isInstanceOf($array, $this)) {
            $array = $array->val();
        }

        $this->val(array_merge($this->val(), $array));

        return $this;
    }

    /**
     * Merge given $array with current array recursively.
     * If two arrays contain same keys, their values wil be merged together
     *
     * @param array|ArrayObject $array
     *
     * @return $this
     */
    public function mergeRecursive($array)
    {
        if ($this->isInstanceOf($array, $this)) {
            $array = $array->val();
        }

        $this->val(array_merge_recursive($this->val(), $array));

        return $this;
    }

    /**
     * Merge given $array with current array using smart algorithm.<br>
     *
     * This will result in the most logical array:
     * - simple values will be overwritten
     * - numeric index arrays will be appended to the original array<br>
     * (numeric indexes are not replacing the original array index, their values are simply appended to the original array)
     * - associative arrays will be merged
     *
     * This method works exactly the same way as Config component mergeWith($config) method.
     *
     * @param array|ArrayObject $array
     *
     * @return $this
     * @throws ArrayObjectException
     */
    public function mergeSmart($array)
    {
        if ($this->isArray($array)) {
            $array = new ArrayObject($array);
        } elseif (!$this->isArrayObject($array)) {
            throw new ArrayObjectException(ArrayObjectException::MSG_INVALID_PARAM, [
                '$array',
                'ArrayObject or array'
            ]
            );
        }

        /** @var ArrayObject $array */
        foreach ($array as $key => $value) {
            if (array_key_exists($key, $this->_value)) {
                if ($this->isNumber($key)) {
                    $this->_value[] = $value;
                } elseif (is_array($value) || $this->isInstanceOf($value, $this)) {
                    $this->_value[$key] = (new self($this->_value[$key]))->mergeSmart($value)->val();
                } else {
                    $this->_value[$key] = $value;
                }
            } else {
                if ($this->isInstanceOf($value, $this)) {
                    $this->_value[$key] = $value->val();
                } else {
                    $this->_value[$key] = $value;
                }
            }
        }

        return $this;
    }

    /**
     * Sort an array by values using a user-defined comparison function<br />
     * This function assigns new keys to the elements in array. It will remove any existing keys that may have been assigned, rather than just reordering the keys.<br />
     * The comparison function must return an integer less than, equal to, or greater than zero if the first argument is considered to be respectively less than, equal to, or greater than the second.
     *
     * @param callable $comparisonFunction
     *
     * @throws ArrayObjectException
     *
     * @return $this
     */
    public function sortUsingFunction($comparisonFunction)
    {
        if (!is_callable($comparisonFunction)) {
            throw new ArrayObjectException(ArrayObjectException::MSG_INVALID_ARG, [
                                                                                    '$comparisonFunction',
                                                                                    'callable'
                                                                                ]
            );
        }

        $val = $this->val();
        usort($val, $comparisonFunction);
        $this->val($val);

        return $this;
    }

    /**
     * Sort the array by its values (does not maintain index association).
     * This sort function take two flags. One defines the sort algorithm, and the other one, the sort direction.
     * Default behavior equals to the standard sort function.
     *
     * @param int $direction In which direction you want to sort. You can use SORT_ASC or SORT_DESC.
     * @param int $sortFlag  Which sort algorithm. SORT_REGULAR | SORT_NUMERIC | SORT_STRING | SORT_LOCALE_STRING | SORT_NATURAL
     *
     * @return $this
     * @throws ArrayObjectException
     */
    public function sort($direction = SORT_ASC, $sortFlag = SORT_REGULAR)
    {
        try {
            $arr = $this->val();
            if ($direction == SORT_ASC) {
                sort($arr, $sortFlag);
            } else {
                rsort($arr, $sortFlag);
            }
        } catch (\ErrorException $e) {
            throw new ArrayObjectException($e->getMessage());
        }

        $this->val($arr);

        return $this;
    }

    /**
     * Sort the array by its values (maintains key association).
     * This sort function take two flags. One defines the sort algorithm, and the other one, the sort direction.
     * Default behavior equals to the standard asort function.
     *
     * @param int $direction In which direction you want to sort. You can use SORT_ASC or SORT_DESC.
     * @param int $sortFlag  Which sort algorithm. SORT_REGULAR | SORT_NUMERIC | SORT_STRING | SORT_LOCALE_STRING | SORT_NATURAL
     *
     * @return $this
     * @throws ArrayObjectException
     */
    public function sortAssoc($direction = SORT_ASC, $sortFlag = SORT_REGULAR)
    {
        try {
            $arr = $this->val();
            if ($direction == SORT_ASC) {
                asort($arr, $sortFlag);
            } else {
                arsort($arr, $sortFlag);
            }
        } catch (\ErrorException $e) {
            throw new ArrayObjectException($e->getMessage());
        }

        $this->val($arr);

        return $this;
    }

    /**
     * Sort the array by its key.
     * This sort function take two flags. One defines the sort algorithm, and the other one, the sort direction.
     * Default behavior equals to the standard asort function.
     *
     * @param int $direction In which direction you want to sort. You can use SORT_ASC or SORT_DESC.
     * @param int $sortFlag  Which sort algorithm. SORT_REGULAR | SORT_NUMERIC | SORT_STRING | SORT_LOCALE_STRING | SORT_NATURAL
     *
     * @return $this
     * @throws ArrayObjectException
     */
    public function sortKey($direction = SORT_ASC, $sortFlag = SORT_REGULAR)
    {
        try {
            $arr = $this->val();
            if ($direction == SORT_DESC) {
                krsort($arr, $sortFlag);
            } else {
                ksort($arr, $sortFlag);
            }
        } catch (\ErrorException $e) {
            throw new ArrayObjectException($e->getMessage());
        }

        $this->val($arr);

        return $this;
    }

    /**
     * Sort an assoc array by a value inside the defined $field.
     *
     * @param string $field
     * @param int    $direction
     * @param int    $sortFlag
     *
     * @throws ArrayObjectException
     * @return $this
     */
    public function sortField($field, $direction = SORT_ASC, $sortFlag = SORT_NUMERIC)
    {
        // check array
        if (!$this->isArray($this->first()->val())) {
            throw new ArrayObjectException(ArrayObjectException::MSG_MULTIDIM_SORT);
        }

        // check if key is present in the array
        if (!$this->first()->key($field)) {
            throw new ArrayObjectException(ArrayObjectException::MSG_VALUE_NOT_PRESENT, [
                                                                                          '$field',
                                                                                          'the current array'
                                                                                      ]
            );
        }

        // do the sorting
        $tempArray = array();
        $thisArray = $this->val();

        foreach ($thisArray as $mk => $m) {
            $tempArray[$m[$field]][] = $mk;
        }

        if ($direction == SORT_DESC) {
            krsort($tempArray, $sortFlag);
        } else {
            ksort($tempArray, $sortFlag);
        }

        $newArray = array();
        foreach ($tempArray as $tk => $tv) {
            foreach ($tv as $ttk => $ttv) {
                $newArray[$ttv] = $thisArray[$ttv];
            }
        }

        $this->val($newArray);
        unset($tempArray);
        unset($thisArray);

        return $this;
    }

    /**
     * Pad array to the specified length with a value.
     *
     * @param int   $size  New size of the array
     * @param mixed $value Value to pad if array is smaller than pad_size.
     *
     * @return $this
     * @throws ArrayObjectException
     */
    public function pad($size, $value)
    {
        try {
            $arr = array_pad($this->val(), $size, $value);
        } catch (\ErrorException $e) {
            throw new ArrayObjectException($e->getMessage());
        }

        $this->val($arr);

        return $this;
    }

    /**
     * Returns random elements from current array.
     *
     * @param int $num How many object you want to return.
     *
     * @return ArrayObject
     * @throws ArrayObjectException
     */
    public function rand($num = 1)
    {
        try {
            $arr = array_rand($this->val(), $num);
        } catch (\ErrorException $e) {
            throw new ArrayObjectException($e->getMessage());
        }

        if (!$this->isArray($arr)) {
            $arr = [$arr];
        }

        return new ArrayObject($arr);
    }

    /**
     * This method replaces the values of current array with the values from $array for the matching keys.
     * If a key from the current array exists in the $replacements array, its value will be replaced by the value
     * from $replacements.
     * If the key exists in $replacements, and not in the current array, it will be created in the current array.
     * If a key only exists in the current array, it will be left as is.
     *
     * If $recursive is TRUE it will recurse into arrays and apply the same process to the inner value.
     *
     * @param array|ArrayObject $replacements
     * @param bool              $recursive
     *
     * @throws ArrayObjectException
     *
     * @return $this
     */
    public function replace($replacements, $recursive = false)
    {
        if ($this->isInstanceOf($replacements, $this)) {
            $replacements = $replacements->val();
        } else {
            if (!$this->isArray($replacements)) {
                throw new ArrayObjectException(ArrayObjectException::MSG_INVALID_PARAM, [
                                                                                          '$replacements',
                                                                                          'array, ArrayObject'
                                                                                      ]
                );
            }
        }

        try {
            if ($recursive) {
                $arr = array_replace_recursive($this->val(), $replacements);
            } else {
                $arr = array_replace($this->val(), $replacements);
            }

        } catch (\ErrorException $e) {
            throw new ArrayObjectException($e->getMessage());
        }

        $this->val($arr);

        return $this;
    }

    /**
     * Reverse the order of elements in the current array.
     *
     * @return $this
     */
    public function reverse()
    {
        $this->val(array_reverse($this->val()));

        return $this;
    }

    /**
     * Slice a portion of current array and discard the remains.
     *
     * @param int  $offset       From where to start slicing.
     * @param int  $length       How many elements to take.
     * @param bool $preserveKeys Do you want to preserve the keys from the current array. Default is true.
     *
     * @return $this
     * @throws ArrayObjectException
     */
    public function slice($offset, $length, $preserveKeys = true)
    {
        if (!$this->isNumber($offset)) {
            throw new ArrayObjectException(ArrayObjectException::MSG_INVALID_PARAM, [
                                                                                      '$offset',
                                                                                      'integer'
                                                                                  ]
            );
        }

        if (!$this->isNumber($length)) {
            throw new ArrayObjectException(ArrayObjectException::MSG_INVALID_PARAM, [
                                                                                      '$length',
                                                                                      'integer'
                                                                                  ]
            );
        }

        try {
            $arr = array_slice($this->val(), $offset, $length, $preserveKeys);
        } catch (\ErrorException $e) {
            throw new ArrayObjectException($e->getMessage());
        }

        $this->val($arr);

        return $this;
    }

    /**
     * Remove a portion of the array and replace it with something else.
     *
     * @param int  $offset      From where to start slicing.
     * @param int  $length      How many elements to take.
     * @param bool $replacement With what to replace the selected portion.
     *
     * @return $this
     * @throws ArrayObjectException
     */
    public function splice($offset, $length, $replacement)
    {
        if (!$this->isNumber($offset)) {
            throw new ArrayObjectException(ArrayObjectException::MSG_INVALID_PARAM, [
                                                                                      '$offset',
                                                                                      'integer'
                                                                                  ]
            );
        }

        if (!$this->isNumber($length)) {
            throw new ArrayObjectException(ArrayObjectException::MSG_INVALID_PARAM, [
                                                                                      '$length',
                                                                                      'integer'
                                                                                  ]
            );
        }

        try {
            $arr = array_splice($this->val(), $offset, $length, $replacement);
        } catch (\ErrorException $e) {
            throw new ArrayObjectException($e->getMessage());
        }

        $this->val($arr);

        return $this;
    }

    /**
     * Remove duplicates from the array.
     *
     * @param int $sortFlag   The optional parameter that may be used to modify the sorting behavior.
     *                        Possible values are: SORT_STRING, SORT_NUMERIC, SORT_REGULAR and SORT_LOCALE_STRING
     *
     * @return $this
     */
    public function unique($sortFlag = SORT_REGULAR)
    {
        $this->val(array_unique($this->val(), $sortFlag));

        return $this;
    }

    /**
     * Applies the user-defined function to each element of the input array.
     * If $recursive the function will recurse into deeper arrays.
     *
     * @param mixed $callable  Which function will be called for each array value.
     * @param bool  $recursive Go into deeper levels of the array. Default: false.
     * @param null  $userData  Optional data that can be passed along to the user callable $function.
     *
     * @return $this
     * @throws ArrayObjectException
     */
    public function walk($callable, $recursive = false, $userData = null)
    {
        if (!$this->isCallable($callable)) {
            throw new ArrayObjectException(ArrayObjectException::MSG_INVALID_PARAM, [
                                                                                      $callable,
                                                                                      'a callable function or method'
                                                                                  ]
            );
        }

        try {
            $arr = $this->val();
            if ($recursive) {
                array_walk_recursive($arr, $callable, $userData);
            } else {
                array_walk($arr, $callable, $userData);
            }
        } catch (\ErrorException $e) {
            throw new ArrayObjectException($e->getMessage());
        }

        $this->val($arr);

        return $this;
    }

    /**
     * Shuffle elements in the array.
     *
     * @return $this
     */
    public function shuffle()
    {
        $this->val(shuffle($this->val()));

        return $this;
    }

    /**
     * Compare two arrays or ArrayObjects and returns an ArrayObject containing all the values
     * from current ArrayObject that are not present in any of the other array.
     *
     * @param      $array       Array to which to compare
     * @param bool $compareKeys Do you want to compare array keys also. Default is false.
     *
     * @return ArrayObject
     * @throws ArrayObjectException
     */
    public function diff($array, $compareKeys = false)
    {
        if ($this->isInstanceOf($array, $this)) {
            $array = $array->val();
        } else {
            if (!$this->isArray($array)) {
                throw new ArrayObjectException(ArrayObjectException::MSG_INVALID_PARAM, [
                                                                                          '$array',
                                                                                          'array, ArrayObject'
                                                                                      ]
                );
            }
        }

        if ($compareKeys) {
            $this->val(array_diff_assoc($this->val(), $array));

            return $this;
        } else {
            $this->val(array_diff($this->val(), $array));

            return $this;
        }
    }

    /**
     * Compare the keys from two arrays or ArrayObjects and returns an ArrayObject containing all the values
     * from current ArrayObject whose keys are not present in any of the other arrays.
     *
     * @param      $array       Array to which to compare
     *
     * @return ArrayObject
     * @throws ArrayObjectException
     */
    public function diffKeys($array)
    {
        if ($this->isInstanceOf($array, $this)) {
            $array = $array->val();
        } else {
            if (!$this->isArray($array)) {
                throw new ArrayObjectException(ArrayObjectException::MSG_INVALID_PARAM, [
                                                                                          '$array',
                                                                                          'array, ArrayObject'
                                                                                      ]
                );
            }
        }

        $this->val(array_diff_key($this->val(), $array));

        return $this;
    }
}