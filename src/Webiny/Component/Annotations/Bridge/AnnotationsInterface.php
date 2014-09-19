<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Annotations\Bridge;

/**
 * Annotations bridge interface.
 *
 * @package         Webiny\Component\Annotations\Bridge
 */

interface AnnotationsInterface
{

    /**
     * Get all annotations for the given class.
     *
     * @param string $class Fully qualified class name
     *
     * @return array An associative array with all annotations.
     */
    public function getClassAnnotations($class);

    /**
     * Get all annotations for the property name on the given class.
     *
     * @param string $class    Fully qualified class name
     * @param string $property Property name
     *
     * @return array An associative array with all annotations.
     */
    public function getPropertyAnnotations($class, $property);

    /**
     * Get all annotations for the method name on the given class.
     *
     * @param string $class  Fully qualified class name
     * @param string $method Method name
     *
     * @return array An associative array with all annotations.
     */
    public function getMethodAnnotations($class, $method);
}