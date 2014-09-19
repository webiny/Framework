<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Annotations;


/**
 * Annotations trait provides traits methods for easier access to Annotations component.
 *
 * @package         Webiny\Component\Annotations
 */
trait AnnotationsTrait
{
    /**
     * Get all annotations for the given class.
     *
     * @param string $class Fully qualified class name
     *
     * @return ConfigObject ConfigObject instance containing all annotations.
     */
    protected function annotationsFromClass($class)
    {
        return Annotations::getClassAnnotations($class);
    }

    /**
     * Get all annotations for the property name on the given class.
     *
     * @param string $class    Fully qualified class name
     * @param string $property Property name
     *
     * @return ConfigObject ConfigObject instance containing all annotations.
     */
    protected function annotationsFromProperty($class, $property)
    {
        return Annotations::getPropertyAnnotations($class, $property);
    }

    /**
     * Get all annotations for the method name on the given class.
     *
     * @param string $class  Fully qualified class name
     * @param string $method Method name
     *
     * @return ConfigObject ConfigObject instance containing all annotations.
     */
    protected function annotationsFromMethod($class, $method)
    {
        return Annotations::getMethodAnnotations($class, $method);
    }
}