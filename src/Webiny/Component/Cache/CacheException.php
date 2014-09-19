<?php

/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Cache;

use Webiny\Component\StdLib\Exception\ExceptionAbstract;

/**
 * Class CacheException.
 * @package Webiny\Component\Cache
 */
class CacheException extends ExceptionAbstract
{
    const MSG_UNSUPPORTED_DRIVER = 101;

    protected static $_messages = [
        101 => 'Driver "%s" is not a valida cache driver.'
    ];
}