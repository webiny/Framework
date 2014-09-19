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
     * Transforms the class name and method name as a part of the api path.
     *
     * @param string $className  Class name.
     * @param string $methodName Method name.
     *
     * @return string
     */
    public static function apiUrl($className, $methodName)
    {
        $className = self::classNameToUrl($className);
        $methodName = self::methodNameToUrl($methodName);

        return $className . '/' . $methodName;
    }

    /**
     * Creates a name for the cache class file based on the class name and the version.
     *
     * @param string $className
     *
     * @return string
     */
    public static function classCacheFolder($className)
    {
        return self::str($className)->trimLeft('\\')->replace('\\', '_')->val();
    }

    /**
     * Returns a version cache filename, based on the given $version.
     *
     * @param string $version Version number or name.
     *
     * @return string
     */
    public static function versionCacheFilename($version)
    {
        if (!is_numeric(substr($version, 0, 1))) {
            $versionFile = $version . '.php';
        } else {
            $versionFile = 'v' . $version . '.php';
        }

        return $versionFile;
    }

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
     *
     * @return string
     */
    public static function classNameToUrl($className)
    {
        $className = self::str($className)->explode('\\')->last()->val();
        $url = preg_replace('/([A-Z])/', ' $1', $className);
        $url = self::str($url)->trim()->replace(' ', '-')->caseLower()->val();

        return $url;
    }
}