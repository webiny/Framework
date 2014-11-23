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
    private $_reflectionClass;

    /**
     * @var string Fully qualified name of the api class.
     */
    private $_class;

    /**
     * @var ParsedClass
     */
    private $_parsedClass;

    /**
     * @var bool Should the class name and the method name be normalized.
     */
    private $_normalize;


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
            $this->_reflectionClass = new \ReflectionClass($class);
        } catch (\Exception $e) {
            throw new RestException('Parser: Unable to parse class "' . $class . '". ' . $e->getMessage());
        }

        $this->_normalize = $normalize;
        $this->_class = $class;
        $this->_parsedClass = new ParsedClass($class);

        // parse class
        $this->_parseClass();

        // parse methods
        $this->_parseMethods();

        // cleanup
        unset($this->_reflectionClass);
    }

    /**
     * Get the ParsedClass instance.
     *
     * @return ParsedClass
     */
    public function getParsedClass()
    {
        return $this->_parsedClass;
    }

    /**
     * Internal method that does the actual parsing of class properties.
     */
    private function _parseClass()
    {
        // check which interfaces are implemented
        $interfaces = $this->_reflectionClass->getInterfaceNames();
        foreach ($interfaces as $i) {
            if ($i == 'Webiny\Component\Rest\Interfaces\AccessInterface') {
                $this->_parsedClass->accessInterface = true;
            }

            if ($i == 'Webiny\Component\Rest\Interfaces\CacheKeyInterface') {
                $this->_parsedClass->cacheKeyInterface = true;
            }
        }
    }

    /**
     * Parsed the class methods and assigns them to the ParsedClass instance.
     *
     * @throws RestException
     */
    private function _parseMethods()
    {
        $methods = $this->_reflectionClass->getMethods();
        if (!is_array($methods) || count($methods) < 1) {
            throw new RestException('Parser: The class "' . $this->_class . '" doesn\'t have any methods defined.');
        }

        foreach ($methods as &$m) {
            $methodParser = new MethodParser($this->_class, $m, $this->_normalize);
            $parsedMethod = $methodParser->parse();
            if ($parsedMethod) {
                $this->_parsedClass->addApiMethod($methodParser->parse());
            }
        }
    }
}