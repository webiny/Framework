<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */
namespace Webiny\Component\EventManager;

use Webiny\Component\StdLib\Exception\AbstractException;

/**
 * Exception class for the EventManager component.
 *
 * @package         Webiny\Component\EventManager
 */
class EventManagerException extends AbstractException
{
    const INVALID_PRIORITY_VALUE = 101;
    const INVALID_EVENT_HANDLER = 102;
    const INVALID_EVENT_NAME = 103;

    protected static $messages = [
        101 => 'Event listener priority must be greater than 100 and smaller than 1000.',
        102 => 'Event handler must be a valid callable, class name or class instance.',
        103 => 'Event name must be a string at least 1 character long.'
    ];

}