<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @link         http://www.webiny.com/wf-snv for the canonical source repository
 * @copyright    Copyright (c) 2009-2013 Webiny LTD. (http://www.webiny.com)
 * @license      http://www.webiny.com/framework/license
 * @package      WebinyFramework
 */
namespace Webiny\Component\Logger;

use Webiny\Component\StdLib\Exception\ExceptionAbstract;

/**
 * Logger exception class.
 *
 * @package      Webiny\Component\Logger
 */
class LoggerException extends ExceptionAbstract
{
    const FORMATTER_CONFIG_NOT_FOUND = 101;
    const HANDLER_CONFIG_NOT_FOUND = 102;

    protected static $messages = [
        101 => 'Formatter `%s`config was not found!',
        102 => 'Handler `%s`config was not found!'
    ];
}