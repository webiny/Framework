<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @link      http://www.webiny.com/wf-snv for the canonical source repository
 * @copyright Copyright (c) 2009-2013 Webiny LTD. (http://www.webiny.com)
 * @license   http://www.webiny.com/framework/license
 * @package   WebinyFramework
 */

namespace Webiny\Component\StdLib;

use Webiny\Component\StdLib\StdObject\StdObjectWrapper;

/**
 * Trait containing common validators.
 *
 * @package         Webiny\Component\StdLib
 */
trait ValidatorTrait
{
    protected static function is($var)
    {
        if (isset($var)) {
            return true;
        }

        return false;
    }

    /**
     * Checks if given value is null.
     *
     * @param mixed $var Value to check
     *
     * @return bool
     */
    protected static function isNull($var)
    {
        return is_null($var);
    }

    /**
     * Checks if given value is empty.
     *
     * @param mixed $var Value to check
     *
     * @return bool
     */
    protected static function isEmpty($var)
    {
        return empty($var);
    }

    /**
     * Check if given value is an object.
     *
     * @param mixed $var Value to check
     *
     * @return bool
     */
    protected static function isObject($var)
    {
        return is_object($var);
    }

    /**
     * Check if given value is a scalar value.
     * Scalar values are: integer, float, boolean and string
     *
     * @param mixed $var Value to check
     *
     * @return bool
     */
    protected static function isScalar($var)
    {
        return is_scalar($var);
    }

    /**
     * Check if given value is a resource.
     *
     * @param mixed $var Value to check
     *
     * @return bool
     */
    protected static function isResource($var)
    {
        return is_resource($var);
    }

    /**
     * Checks if given value is an array.
     *
     * @param $var
     *
     * @return bool
     */
    protected static function isArray($var)
    {
        return is_array($var);
    }

    /**
     * Checks if value is a number.
     *
     * @param $var
     *
     * @return bool
     */
    protected static function isNumber($var)
    {
        return is_numeric($var);
    }

    /**
     * Checks if value is an integer.
     *
     * @param $var
     *
     * @return bool
     */
    protected static function isInteger($var)
    {
        return is_int($var);
    }

    /**
     * Checks whenever resource is callable.
     *
     * @param $var
     *
     * @return bool
     */
    protected static function isCallable($var)
    {
        return is_callable($var);
    }

    /**
     * Checks if $var is type of string.
     *
     * @param $var
     *
     * @return bool
     */
    protected static function isString($var)
    {
        return is_string($var);
    }

    /**
     * Checks if $var is type of boolean.
     *
     * @param $var
     *
     * @return bool
     */
    protected static function isBool($var)
    {
        return is_bool($var);
    }

    /**
     * This is an alias function for self::isBool
     *
     * @param $var
     *
     * @return bool
     */
    protected static function isBoolean($var)
    {
        return self::isBool($var);
    }

    /**
     * Checks if $var is a file.
     *
     * @param $var
     *
     * @return bool
     */
    protected static function isFile($var)
    {
        return is_file($var);
    }

    /**
     * Checks if $var is readable.
     *
     * @param $var
     *
     * @return bool
     */
    protected static function isReadable($var)
    {
        return is_readable($var);
    }

    /**
     * Checks if $var is a directory.
     *
     * @param $var
     *
     * @return bool
     */
    protected static function isDirectory($var)
    {
        return is_dir($var);
    }

    /**
     * Check if $instance is of $type.
     *
     * @param mixed $instance
     * @param string $type
     *
     * @return bool
     */
    protected static function isInstanceOf($instance, $type)
    {
        return ($instance instanceof $type);
    }

    /**
     * Check if $subclass is a subclass of $class.
     *
     * @param string|object $subclass
     * @param string $class
     *
     * @return bool
     */
    protected static function isSubClassOf($subclass, $class)
    {
        return is_subclass_of($subclass, $class);
    }

    /**
     * Check if $instance is a StandardObject.
     *
     * @param mixed $instance
     *
     * @return bool
     */
    protected static function isStdObject($instance)
    {
        if (self::isInstanceOf($instance, 'Webiny\Component\StdLib\StdObject\AbstractStdObject')) {
            return true;
        }

        return false;
    }

    /**
     * Check if $instance is a StringObject.
     *
     * @param mixed $instance
     *
     * @return bool
     */
    protected static function isStringObject($instance)
    {
        return StdObjectWrapper::isStringObject($instance);
    }

    /**
     * Check if $instance is a DateTimeObject.
     *
     * @param mixed $instance
     *
     * @return bool
     */
    protected static function isDateTimeObject($instance)
    {
        return StdObjectWrapper::isDateTimeObject($instance);
    }

    /**
     * Check if $instance is a FileObject.
     *
     * @param mixed $instance
     *
     * @return bool
     */
    protected static function isFileObject($instance)
    {
        return StdObjectWrapper::isFileObject($instance);
    }

    /**
     * Check if $instance is an ArrayObject.
     *
     * @param mixed $instance
     *
     * @return bool
     */
    protected static function isArrayObject($instance)
    {
        return StdObjectWrapper::isArrayObject($instance);
    }

    /**
     * Check if $instance is a UrlObject.
     *
     * @param mixed $instance
     *
     * @return bool
     */
    protected static function isUrlObject($instance)
    {
        return StdObjectWrapper::isUrlObject($instance);
    }

    /**
     * Checks if class exists.
     * This function autoloads classes to checks if they exist.
     *
     * @param string $className Class name with their full namespace.
     *
     * @return bool
     */
    protected static function classExists($className)
    {
        return class_exists($className, true);
    }

    /**
     * Checks if given object $instance has the given method.
     *
     * @param object $instance   Object instance.
     * @param string $methodName Name of the method you wish to check.
     *
     * @return bool
     */
    protected static function methodExists($instance, $methodName)
    {
        return method_exists($instance, $methodName);
    }
}