<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Router\Route;

use Webiny\Component\StdLib\StdLibTrait;

/**
 * RouteCompiler compiles a Route and returns CompiledRoute instance.
 *
 * @package         Webiny\Component\Router\Route
 */
class RouteCompiler
{
    use StdLibTrait;

    /**
     * List of characters that are considered to be regex separators.
     */
    const SEPARATORS = '/,;.:-_~+*=@|';


    /**
     * Compiles the route and returns an instance of CompiledRoute.
     *
     * @param Route $route Route to compile.
     *
     * @return CompiledRoute
     */
    public static function compile(Route $route)
    {
        $staticPrefix = false;
        $variables = [];
        $pos = 0;
        $extractedRegexes = [];
        $defaults = [];

        // set correct static prefix
        $prefix = $route->getHost() == '' ? '' : $route->getHost() . '/';

        // route regex
        $routePatternObject = self::str($route->getPath())->trimLeft('/');
        $routePattern = $prefix . $routePatternObject->val() . '$';
        // we append the regex to match the string from beginning only if the path starts with http
        if($routePatternObject->startsWith('http')) {
            $routePattern = '^' . $routePattern;
        }
        // set regex delimiters
        $routePattern = '#' . $routePattern . '#';

        // extract all variables
        preg_match_all('#\{\w+\}#', $route->getPath(), $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
        foreach ($matches as $m) {
            $var = substr($m[0][0], 1, -1);

            // get all the text before the variable
            if(!$staticPrefix) {
                $prefix = substr($route->getPath(), $pos, ($m[0][1] - $pos));
                $pos = $m[0][1] + strlen($m[0][0]);
                $precedingChar = strlen($prefix) > 0 ? substr($prefix, -1) : '';
                if(strlen($precedingChar) === 1 && strpos(self::SEPARATORS, $precedingChar) !== false) {
                    $staticPrefix .= substr($prefix, 0, -1);
                } else {
                    $staticPrefix .= $prefix;
                }
            }

            $regex = '[\w-]+';
            $default = false;
            if($route->hasOption($var)) {
                // pattern
                if($route->getOptions()[$var]->hasAttribute('Pattern')) {
                    $regex = $route->getOptions()[$var]->getAttribute('Pattern');
                }

                // default
                if($route->getOptions()[$var]->hasAttribute('Default')) {
                    $default = $route->getOptions()[$var]->getAttribute('Default');
                }
            }
            $extractedRegexes[$var] = $regex;
            $defaults['{' . $var . '}'] = $default;

            $variables[] = ['name' => $var];
            $routePattern = str_replace($m[0][0], '(' . $regex . ')', $routePattern);
        }

        // build the default route
        $defaultRoute = false;
        if(count($defaults) > 0) {
            $defaultRoute = str_replace(array_keys($defaults), array_values($defaults), $route->getPath());
        }

        return new CompiledRoute($staticPrefix, $routePattern, $variables, $extractedRegexes, $defaultRoute);
    }

}