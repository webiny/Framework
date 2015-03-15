<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\StdLib\StdObject\StringObject;

use Webiny\Component\StdLib\StdObject\StdObjectException;

/**
 * StringObject exception class.
 *
 * @package         Webiny\Component\StdLib\StdObject\StringObject
 */
class StringObjectException extends StdObjectException
{

    const MSG_UNABLE_TO_EXPLODE = 101;
    const MSG_INVALID_HASH_ALGO = 102;

    protected static $messages = [
        101 => 'Unable to explode the string with the given delimiter "%s".',
        102 => 'Invalid hash algorithm provided: "%s". Visit http://www.php.net/manual/en/function.hash-algos.php for more information.'
    ];

}