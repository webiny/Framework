<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Router;

use Webiny\Component\StdLib\Exception\AbstractException;

/**
 * Exception class for Router component.
 *
 * @package         Webiny\Component\Router
 */
class RouterException extends AbstractException
{
    const STRING_CALLBACK_NOT_PARSABLE = 101;
    const CALLBACK_CLASS_NOT_FOUND = 102;
    const CALLBACK_CLASS_METHOD_NOT_FOUND = 103;

    protected static $messages = [
        101 => 'Router is unable to parse string callbacks.',
        102 => 'Router callback class `%s` was not found!',
        103 => 'Router callback method `%s` was not found in class `%s`!'
    ];
}