<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Rest\Parser;

/**
 * The result of parsing an api class, including its methods and parameters, is an instance of this class.
 *
 * @package         Webiny\Component\Rest\Parser
 */

class ParsedClass
{
    /**
     * @var string Name of the class.
     */
    public $class;

    /**
     * @var array A list of ParsedMethod instances.
     */
    public $parsedMethods = [];

    /**
     * @var bool A boolean value telling if the class has implemented the cacheKeyInterface.
     */
    public $cacheKeyInterface = false;

    /**
     * @var bool A boolean value telling if the class has implemented the AccessInterface.
     */
    public $accessInterface = false;


    /**
     * Base constructor.
     *
     * @param string $class Name of the class.
     */
    public function __construct($class)
    {
        $this->class = $class;
    }

    /**
     * Adds an instance of ParsedMethod.
     *
     * @param ParsedMethod $parsedMethod
     */
    public function addApiMethod(ParsedMethod $parsedMethod)
    {
        $this->parsedMethods[$parsedMethod->name] = $parsedMethod;
    }

    /**
     * Returns a list of all parsed methods.
     *
     * @return array
     */
    public function getMethods()
    {
        return $this->apiMethods;
    }
}