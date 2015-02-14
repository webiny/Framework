<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @link      http://www.webiny.com/wf-snv for the canonical source repository
 * @copyright Copyright (c) 2009-2013 Webiny LTD. (http://www.webiny.com)
 * @license   http://www.webiny.com/framework/license
 * @package   WebinyFramework
 */

namespace Webiny\Component\StdLib\StdObject\StringObject;

use ArrayObject;
use Webiny\Component\StdLib\StdObject\StdObjectValidatorTrait;
use Webiny\Component\StdLib\StdObject\StdObjectWrapper;

/**
 * StringObject validator trait.
 *
 * @package         Webiny\Component\StdLib\StdObject\StringObject
 */
trait ValidatorTrait
{
    use StdObjectValidatorTrait;

    /**
     * Checks if a string contains the given $char.
     * If the $char is present, true is returned.
     * If you wish to match a string to a regular expression use StringObject:match().
     *
     * @param string|StringObject $needle String you wish to check if it exits within the current string.
     *
     * @return bool True if current string contains the $needle. Otherwise false is returned.
     */
    public function contains($needle)
    {
        $needle = StdObjectWrapper::toString($needle);

        // we double-cast the $needle param to string, because integer is also a string, but in stripos function integer
        // can cause unwanted mismatches if it's not strictly casted to string
        if (stripos($this->val(), (string)$needle) !== false) {
            return true;
        }

        return false;
    }

    /**
     * Checks if a string contains any of the given $char.
     * If any of given $char is present, true is returned.
     *
     * @param array|ArrayObject $needle Array of characters you wish to check
     *
     * @return bool True if current string contains the $needle. Otherwise false is returned.
     */
    public function containsAny($needle)
    {
        $needle = StdObjectWrapper::toArray($needle);

        foreach($needle as $char){
            if($this->contains($char)){
                return true;
            }
        }

        return false;
    }

    /**
     * Check if $string is equal to current string.
     * Note that this comparison is case sensitive and binary safe.
     *
     * @param string|StringObject $string String to compare.
     *
     * @return bool True if current string is equal to $string. Otherwise false is returned.
     */
    public function equals($string)
    {
        $string = StdObjectWrapper::toString($string);

        // we double-cast the $string param to string, because integer is also a string, but in strcmp function integer
        // can cause unwanted mismatches if it's not strictly casted to string
        $result = strcmp((string)$string, $this->val());
        if ($result !== 0) {
            return false;
        }

        return true;
    }

    /**
     * Returns the position of the given $string inside the current string object.
     * Boolean false is returned if the $string is not present inside the current string.
     * NOTE: Use type validation check in order no to mistake the position '0' (zero) for (bool) false.
     *
     * @param string $string
     * @param int    $offset
     *
     * @throws StringObjectException
     * @return int|bool If $string is contained within the current string, the position of $string is returned, otherwise false.
     */
    public function stringPosition($string, $offset = 0)
    {
        $string = StdObjectWrapper::toString($string);

        if (!$this->isNumber($offset)) {
            throw new StringObjectException(StringObjectException::MSG_INVALID_ARG, [
                    '$offset',
                    'integer'
                ]
            );
        }

        // we double-cast the $string param to string, because integer is also a string, but in stripos function integer
        // can cause unwanted mismatches if it's not strictly casted to string
        return stripos($this->val(), (string)$string, $offset);
    }

    /**
     * Checks if the current string starts with the given $string.
     *
     * @param string|StringObject $string String to check.
     *
     * @return bool If current string starts with $string, true is returned, otherwise false.
     */
    public function startsWith($string)
    {
        $string = StdObjectWrapper::toString($string);

        $position = $this->stringPosition($string);
        if ($position !== false && $position == 0) {
            return true;
        }

        return false;
    }

    /**
     * Checks if the current string ends with the given $string.
     *
     * @param string|StringObject $string String to check.
     *
     * @return bool If current string ends with $string, true is returned, otherwise false.
     */
    public function endsWith($string)
    {
        $string = StdObjectWrapper::toString($string);

        // calculate the end position
        $length = strlen($string);
        $endString = substr($this->val(), -$length);

        if ($string == $endString) {
            return true;
        }

        return false;
    }

    /**
     * Checks if the string length is great than the given length.
     *
     * @param int  $num       Length against which you wish to check.
     * @param bool $inclusive Do you want the check to be inclusive or not. Default is false (not inclusive).
     *
     * @throws StringObjectException
     * @return bool If current string size is longer than the given $num, true is returned, otherwise false.
     */
    public function longerThan($num, $inclusive = false)
    {
        if (!$this->isNumber($num)) {
            throw new StringObjectException(StringObjectException::MSG_INVALID_ARG, [
                    '$num',
                    'integer'
                ]
            );
        }

        if (!$this->isBoolean($inclusive)) {
            throw new StringObjectException(StringObjectException::MSG_INVALID_ARG, [
                    '$inclusive',
                    'boolean'
                ]
            );
        }

        $length = strlen($this->val());
        if ($length > $num) {
            return true;
        } else {
            if ($inclusive && $length >= $num) {
                return true;
            }
        }

        return false;
    }
}