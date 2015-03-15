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
    private $staticPrefix;

    /**
     * @var string
     */
    private $regex;

    /**
     * @var array
     */
    private $variables;

    /**
     * @var array
     */
    private $extractedRegexes = [];

    /**
     * @var array
     */
    private $defaultRoute = [];


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
        $this->staticPrefix = $staticPrefix;
        $this->regex = $regex;
        $this->variables = $variables;
        $this->extractedRegexes = $extractedRegexes;
        $this->defaultRoute = $defaultRoute;
    }

    /**
     * Get the static prefix.
     *
     * @return string
     */
    public function getStaticPrefix()
    {
        return $this->staticPrefix;
    }

    /**
     * Get the regular expression to match the url.
     *
     * @return string
     */
    public function getRegex()
    {
        return $this->regex;
    }

    /**
     * Get the extracted variables from the path.
     *
     * @return array
     */
    public function getVariables()
    {
        return $this->variables;
    }

    /**
     * Returns a list of extracted regexes.
     *
     * @return array
     */
    public function getExtractedRegexes()
    {
        return $this->extractedRegexes;
    }

    /**
     * Returns the default route.
     *
     * @return string|bool
     */
    public function getDefaultRoute()
    {
        return $this->defaultRoute;
    }
}