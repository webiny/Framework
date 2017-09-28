<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Security\Token;

use Webiny\Component\StdLib\Exception\AbstractException;

/**
 * Token exception class.
 *
 * @package         Webiny\Component\Security\User\Token
 */
class TokenException extends AbstractException
{
    const TOKEN_EXPIRED = 100;

    protected static $messages = [
        100 => 'Token expired'
    ];
}