<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Security\User\Exceptions;

use Webiny\Component\StdLib\Exception\ExceptionAbstract;

/**
 * User not found exception.
 * This exception is thrown by UserProvider when a user is not found.
 *
 * @package         Webiny\Component\Security\User\Exceptions
 */
class UserNotFoundException extends ExceptionAbstract
{
}