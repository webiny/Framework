<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Rest\Parser;

use Webiny\Component\StdLib\StdLibTrait;

/**
 * This class builds paths from strings based on different rules.
 *
 * @package         Webiny\Component\Rest\Parser
 */
class PathTransformations
{
    use StdLibTrait;

    /**
     * Transforms method name to a url path.
     *
     * @param string $methodName Method name.
     *
     * @return string
     */
    public static function methodNameToUrl($methodName)
    {
        $url = preg_replace('/([A-Z])/', ' $1', $methodName);
        $url = self::str($url)->trim()->replace(' ', '-')->caseLower()->val();

        return $url;
    }

    /**
     * Transforms the class name to url path.
     *
     * @param string $className Class name.
     * @param bool $normalize Should the name be normalized or not
     *
     * @return string
     */
    public static function classNameToUrl($className, $normalize)
    {
        $className = self::str($className)->explode('\\')->last()->val();
        if(!$normalize){
            return $className;
        }
        $url = preg_replace('/([A-Z])/', ' $1', $className);
        $url = self::str($url)->trim()->replace(' ', '-')->caseLower()->val();

        return $url;
    }
}