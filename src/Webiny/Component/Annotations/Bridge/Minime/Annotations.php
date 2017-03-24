<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Annotations\Bridge\Minime;

use Minime\Annotations\Cache\ArrayCache;
use Minime\Annotations\ParserRules;
use Minime\Annotations\Reader;
use Webiny\Component\Annotations\Bridge\AnnotationsInterface;

/**
 * Annotations bridge using Minime\Annotations library.
 *
 * @package         Webiny\Component\Annotations\Bridge\Minime
 */
class Annotations implements AnnotationsInterface
{
    protected $reader;

    public function __construct()
    {
        $this->reader = new Reader(new Parser(), new ArrayCache());
    }

    private function parseAnnotations(\Reflector $reflector)
    {
        return $this->reader->getAnnotations($reflector)->toArray();
    }

    /**
     * Get all annotations for the given class.
     *
     * @param string $class Fully qualified class name
     *
     * @return array An associative array with all annotations.
     */
    public function getClassAnnotations($class)
    {
        return $this->parseAnnotations(new \ReflectionClass($class));
    }

    /**
     * Get all annotations for the property name on the given class.
     *
     * @param string $class    Fully qualified class name
     * @param string $property Property name
     *
     * @return array An associative array with all annotations.
     */
    public function getPropertyAnnotations($class, $property)
    {
        return $this->parseAnnotations(new \ReflectionProperty($class, $property));
    }

    /**
     * Get all annotations for the method name on the given class.
     *
     * @param string $class  Fully qualified class name
     * @param string $method Method name
     *
     * @return array An associative array with all annotations.
     */
    public function getMethodAnnotations($class, $method)
    {
        return $this->parseAnnotations(new \ReflectionMethod($class, $method));
    }
}