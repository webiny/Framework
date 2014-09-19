<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @link         http://www.webiny.com/wf-snv for the canonical source repository
 * @copyright    Copyright (c) 2009-2013 Webiny LTD. (http://www.webiny.com)
 * @license      http://www.webiny.com/framework/license
 * @package      WebinyFramework
 */

namespace Webiny\Component\StdLib;

/**
 * Helper trait with some standard functions.
 *
 * @package         Webiny\Component\StdLib
 */
trait StdLibTrait
{
    use StdObjectTrait, ValidatorTrait;

    /**
     * @param mixed $value The value being encoded. Can be any type except a resource. This function only works with UTF-8 encoded data.
     * @param int   $options
     *
     * @return string|boolean A JSON encoded string on success or FALSE on failure.
     */
    protected static function jsonEncode($value, $options = 0)
    {
        return json_encode($value, $options);
    }

    /**
     * Decode JSON string
     *
     * @param string $json    The json string being decoded. This function only works with UTF-8 encoded data.
     * @param bool   $assoc   When TRUE, returned objects will be converted into associative arrays.
     *
     * @param int    $depth   User specified recursion depth.
     * @param int    $options Bitmask of JSON decode options. Currently only JSON_BIGINT_AS_STRING is supported (default is to cast large integers as floats)
     *
     * @return mixed The value encoded in json in appropriate PHP type. NULL is returned if the json cannot be decoded or if the encoded data is deeper than the recursion limit.
     */
    protected static function jsonDecode($json, $assoc = false, $depth = 512, $options = 0)
    {
        return json_decode($json, $assoc, $depth, $options);
    }

    /**
     * Serializes the given array.
     *
     * @param array $array Array to serialize.
     *
     * @return string
     */
    protected static function serialize(array $array)
    {
        return serialize($array);
    }

    /**
     * Unserializes the given string and returns the array.
     *
     * @param string $string String to serialize.
     *
     * @return array|mixed
     */
    protected static function unserialize($string)
    {
        if (is_array($string)) {
            return $string;
        }

        if (($data = unserialize($string)) !== false) {
            return $data;
        }

        return unserialize(stripslashes($string));
    }
}