<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\StdLib\StdObject\DateTimeObject;

use Webiny\Component\StdLib\StdObject\StdObjectException;

/**
 * DateTimeObject exception class.
 *
 * @package         Webiny\Component\StdLib\StdObject\DateTimeObject
 */
class DateTimeObjectException extends StdObjectException
{
    const MSG_INVALID_TIMEZONE = 101;
    const MSG_UNABLE_TO_CREATE_FROM_FORMAT = 102;
    const MSG_UNABLE_TO_PARSE = 103;
    const MSG_UNABLE_TO_DIFF = 104;
    const MSG_INVALID_DATE_FORMAT = 105;
    const MSG_DEFAULT_TIMEZONE = 106;
    const MSG_INVALID_FORMAT_FOR_ELEMENT = 107;
    const MSG_INVALID_DATE_INTERVAL = 108;
    const MSG_MONGO_EXTENSION_REQUIRED = 109;

    protected static $messages = [
        101 => 'Invalid timezone provided "%s".',
        102 => 'Unable to create date from the given $time and $format',
        103 => 'Unable to parse %s param.',
        104 => 'Unable to diff the two dates.',
        105 => 'Invalid date format "%s".',
        106 => 'Unable to detect the default timezone.',
        107 => 'Invalid format %s for %s',
        108 => 'Invalid datetime interval provided "%s".',
        109 => 'getMongoDate() method requires Mongo PHP extension'
    ];
}