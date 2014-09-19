<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\StdLib\StdObject\UrlObject;

use Webiny\Component\StdLib\StdObject\StdObjectException;

/**
 * UrlObject exception class.
 *
 * @package         Webiny\Component\StdLib\StdObject\UrlObject
 */
class UrlObjectException extends StdObjectException
{
    const MSG_INVALID_URL = 101;

    protected static $_messages = [
        101 => 'Unable to parse "%s" as a valid url.'
    ];
}
