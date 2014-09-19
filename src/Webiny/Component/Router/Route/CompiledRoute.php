<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Router\Route;

/**
 * CompiledRoute contains the extracted patterns and compiled regexes for matching url.
 *
 * @package         Webiny\Component\Router\Route
 */

class CompiledRoute
{
    /**
     * @var string
     */
    private $_staticPrefix;

    /**
     * @var string
     */
    private $_regex;

    /**
     * @var array
     */
    private $_variables;

    /**
     * @var array
     */
    private $_extractedRegexes = [];

    /**
     * @var array
     */
    private $_defaultRoute = [];


    /**
     * Base constructor.
     *
     * @param string      $staticPrefix     Pattern prefix that doesn't contain regular expression.
     * @param string      $regex            Regular expression that will be matched against the given url.
     * @param array       $variables        List of available variables extracted from the route path.
     * @param array       $extractedRegexes List of extracted regexes from the route.
     * @param string|bool $defaultRoute     A route that contains default values or false.
     */
    public function __construct($staticPrefix, $regex, array $variables, array $extractedRegexes, $defaultRoute)
    {
        $this->_staticPrefix = $staticPrefix;
        $this->_regex = $regex;
        $this->_variables = $variables;
        $this->_extractedRegexes = $extractedRegexes;
        $this->_defaultRoute = $defaultRoute;
    }

    /**
     * Get the static prefix.
     *
     * @return string
     */
    public function getStaticPrefix()
    {
        return $this->_staticPrefix;
    }

    /**
     * Get the regular expression to match the url.
     *
     * @return string
     */
    public function getRegex()
    {
        return $this->_regex;
    }

    /**
     * Get the extracted variables from the path.
     *
     * @return array
     */
    public function getVariables()
    {
        return $this->_variables;
    }

    /**
     * Returns a list of extracted regexes.
     *
     * @return array
     */
    public function getExtractedRegexes()
    {
        return $this->_extractedRegexes;
    }

    /**
     * Returns the default route.
     *
     * @return string|bool
     */
    public function getDefaultRoute()
    {
        return $this->_defaultRoute;
    }
}