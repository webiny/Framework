<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Rest\Parser;

use Webiny\Component\Rest\RestException;

/**
 * ClassParser parses api class parameters
 *
 * @package         Webiny\Component\Rest\Parser
 */
class ClassParser
{
    /**
     * @var \ReflectionClass A \ReflectionClass instance of the $class.
     */
    private $reflectionClass;

    /**
     * @var string Fully qualified name of the api class.
     */
    private $class;

    /**
     * @var ParsedClass
     */
    private $parsedClass;

    /**
     * @var bool Should the class name and the method name be normalized.
     */
    private $normalize;


    /**
     * Base constructor.
     *
     * @param string $class Fully qualified name of the api class.
     * @param bool   $normalize Should the class name and the method name be normalized.
     *
     * @throws RestException
     */
    public function __construct($class, $normalize)
    {
        // first we check how many version the api has
        try {
            $this->reflectionClass = new \ReflectionClass($class);
        } catch (\Exception $e) {
            throw new RestException('Parser: Unable to parse class "' . $class . '". ' . $e->getMessage());
        }

        $this->normalize = $normalize;
        $this->class = $class;
        $this->parsedClass = new ParsedClass($class);

        // parse class
        $this->parseClass();

        // parse methods
        $this->parseMethods();

        // cleanup
        unset($this->reflectionClass);
    }

    /**
     * Get the ParsedClass instance.
     *
     * @return ParsedClass
     */
    public function getParsedClass()
    {
        return $this->parsedClass;
    }

    /**
     * Internal method that does the actual parsing of class properties.
     */
    private function parseClass()
    {
        // check which interfaces are implemented
        $interfaces = $this->reflectionClass->getInterfaceNames();
        foreach ($interfaces as $i) {
            if ($i == 'Webiny\Component\Rest\Interfaces\AccessInterface') {
                $this->parsedClass->accessInterface = true;
            }

            if ($i == 'Webiny\Component\Rest\Interfaces\CacheKeyInterface') {
                $this->parsedClass->cacheKeyInterface = true;
            }
        }
    }

    /**
     * Parsed the class methods and assigns them to the ParsedClass instance.
     *
     * @throws RestException
     */
    private function parseMethods()
    {
        $methods = $this->reflectionClass->getMethods();
        if (!is_array($methods) || count($methods) < 1) {
            throw new RestException('Parser: The class "' . $this->class . '" doesn\'t have any methods defined.');
        }

        foreach ($methods as $m) {
            if ($m->isPublic()) {
                $methodParser = new MethodParser($this->class, $m, $this->normalize);
                $parsedMethod = $methodParser->parse();
                if ($parsedMethod) {
                    $this->parsedClass->addApiMethod($parsedMethod);
                }
            }
        }
    }
}