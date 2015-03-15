<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Annotations;

use Webiny\Component\Config\ConfigObject;
use Webiny\Component\StdLib\ComponentTrait;

/**
 * Annotations component.
 * This component provides methods for reading annotations assigned to a class, method or property.
 *
 * @package         Webiny\Component\Annotations
 */
class Annotations
{
    use ComponentTrait;

    /**
     * Get all annotations for the given class.
     *
     * @param string $class Fully qualified class name
     *
     * @return ConfigObject ConfigObject instance containing all annotations.
     */
    public static function getClassAnnotations($class)
    {
        $annotationBag = Bridge\Loader::getInstance()->getClassAnnotations($class);
        $annotationBag = self::explodeNamespaces($annotationBag);

        return new ConfigObject($annotationBag);
    }

    /**
     * Get all annotations for the property name on the given class.
     *
     * @param string $class    Fully qualified class name
     * @param string $property Property name
     *
     * @return ConfigObject ConfigObject instance containing all annotations.
     */
    public static function getPropertyAnnotations($class, $property)
    {
        $annotationBag = Bridge\Loader::getInstance()->getPropertyAnnotations($class, str_replace('$', '', $property));
        $annotationBag = self::explodeNamespaces($annotationBag);

        return new ConfigObject($annotationBag);
    }

    /**
     * Get all annotations for the method name on the given class.
     *
     * @param string $class  Fully qualified class name
     * @param string $method Method name
     *
     * @return ConfigObject ConfigObject instance containing all annotations.
     */
    public static function getMethodAnnotations($class, $method)
    {
        $annotationBag = Bridge\Loader::getInstance()->getMethodAnnotations($class, $method);
        $annotationBag = self::explodeNamespaces($annotationBag);

        return new ConfigObject($annotationBag);
    }

    /**
     * Converts the dotted annotations inside array key names into a multidimensional array.
     *
     * @param array $annotationBag
     *
     * @return array
     */
    private static function explodeNamespaces($annotationBag)
    {
        foreach ($annotationBag as $k => $v) {
            // fix for empty newlines that cause that a "\n/" is appended to the annotation value
            if (!is_array($v)) {
                $v = str_replace("\n/", "", trim($v));
            }

            if (strpos($k, ".") !== false) {
                unset($annotationBag[$k]);
                self::setArrayValue($annotationBag, $k, $v);
            } else {
                $annotationBag[$k] = $v;
            }
        }

        return $annotationBag;
    }

    /**
     * Changes the dotted annotation of one key into a multidimensional array.
     *
     * @param array  $root         Array on which the conversion is done.
     * @param string $compositeKey The dotted key.
     * @param string $value        Value of the key.
     */
    private static function setArrayValue(&$root, $compositeKey, $value)
    {
        $keys = explode('.', $compositeKey);
        while (count($keys) > 1) {
            $key = array_shift($keys);
            if (!isset($root[$key])) {
                $root[$key] = [];
            }
            $root = &$root[$key];
        }

        $key = reset($keys);
        $root[$key] = $value;
    }
}