<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @link      http://www.webiny.com/wf-snv for the canonical source repository
 * @copyright Copyright (c) 2009-2013 Webiny LTD. (http://www.webiny.com)
 * @license   http://www.webiny.com/framework/license
 * @package   WebinyFramework
 */

namespace Webiny\Component\StdLib;

/**
 * Standard object trait. Use this functions whenever you want to create a standard object.
 *
 * @package         Webiny\Component\StdLib
 */
trait StdObjectTrait
{
    /**
     * Creates an instance of String Standard Object.
     *
     * @param string $string
     *
     * @return StdObject\StringObject\StringObject
     */
    protected static function str($string)
    {
        return new StdObject\StringObject\StringObject($string);
    }

    /**
     * Creates an instance of Array Standard Object.
     *
     * @param array $array
     *
     * @return StdObject\ArrayObject\ArrayObject
     */
    protected static function arr($array = null)
    {
        return new StdObject\ArrayObject\ArrayObject($array);
    }

    /**
     * Creates an instance of Url Standard Object.
     *
     * @param string $url
     *
     * @return StdObject\UrlObject\UrlObject
     */
    protected static function url($url)
    {
        return new StdObject\UrlObject\UrlObject($url);
    }

    /**
     * Create an instance of DateTime Standard Object.
     *
     * @param string|int  $time                     A date/time string. List of available formats is explained here
     *                                              http://www.php.net/manual/en/datetime.formats.php
     * @param null|string $timezone                 Timezone in which you want to set the date. Here is a list of valid
     *                                              timezones: http://php.net/manual/en/timezones.php
     *
     * @return StdObject\DateTimeObject\DateTimeObject
     */
    protected static function datetime($time = "now", $timezone = null)
    {
        return new StdObject\DateTimeObject\DateTimeObject($time, $timezone);
    }
}