<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @link         http://www.webiny.com/wf-snv for the canonical source repository
 * @copyright    Copyright (c) 2009-2013 Webiny LTD. (http://www.webiny.com)
 * @license      http://www.webiny.com/framework/license
 * @package      WebinyFramework
 */
namespace Webiny\Component\Config;

use Webiny\Component\StdLib\Exception\ExceptionAbstract;

/**
 * Config exception class.
 *
 * @package      Webiny\Component\Config
 */
class ConfigException extends ExceptionAbstract
{
    const COULD_NOT_SAVE_CONFIG_FILE = 101;
    const CONFIG_FILE_DOES_NOT_EXIST = 102;

    protected static $_messages = [
        101 => 'Could not save config file.',
        102 => 'Invalid $destination argument! File does not exist.'
    ];

}