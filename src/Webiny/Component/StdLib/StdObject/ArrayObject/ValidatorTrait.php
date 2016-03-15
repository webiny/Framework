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

use Webiny\Component\StdLib\StdObject\StdObjectWrapper;
use Webiny\Component\StdLib\StdObject\StringObject\StringObject;

/**
 * Validator methods for array standard object.
 *
 * @package         Webiny\Component\StdLib\StdObject\ArrayObject
 */
trait ValidatorTrait
{

    /**
     * Search the array for the given $value.
     * If $strict is true, both values must be of the same instance type.
     *
     * @param mixed $value
     * @param bool  $strict
     *
     * @return bool|key Returns the key under which the $value is found, or false.
     */
    public function inArray($value, $strict = false)
    {
        return in_array($value, $this->val(), $strict);
    }

    /**
     * Checks if $key exists in current array as index. If it exists, true is returned.
     * If the $key doesn't exist, $default is returned,
     *
     * @param string|StringObject $key Array key.
     * @param mixed               $default If key is not found, $default is returned.
     *
     * @return bool|mixed True is returned if the key exists, otherwise $default is returned.
     */
    public function keyExists($key, $default = false)
    {
        $key = StdObjectWrapper::toString($key);
        if (array_key_exists($key, $this->val())) {
            return true;
        }

        return $default;
    }

    /**
     * Check if all given keys exist in the array
     *
     * @param array $keys
     *
     * @return bool
     */
    public function keysExist($keys = [])
    {
        foreach ($keys as $key) {
            if (!$this->keyExists($key)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Checks if $key exists in current array as index. If it exists, true is returned.
     * If the $key doesn't exist, $default is returned.
     * This method supports nested keys access: 'level1.level2.level3'
     *
     * @param string|StringObject $key Array key. Eg: 'level1.level2.level3'
     * @param mixed               $default If key is not found, $default is returned.
     *
     * @return bool|mixed True is returned if the key exists, otherwise $default is returned.
     */
    public function keyExistsNested($key, $default = false)
    {
        $key = StdObjectWrapper::toString($key);

        if (strpos($key, '.') !== false) {
            $keys = explode('.', trim($key, '.'), 2);
            if (!isset($this->val()[$keys[0]])) {
                return $default;
            }

            try {
                $sourceArray = new ArrayObject($this->val()[$keys[0]]);
            } catch (ArrayObjectException $e) {
                return $default;
            }

            return $sourceArray->keyExistsNested($keys[1], $default);
        }

        if (array_key_exists($key, $this->val())) {
            return true;
        }

        return $default;
    }
}