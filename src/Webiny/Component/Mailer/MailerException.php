<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Mailer;

use Webiny\Component\StdLib\Exception\AbstractException;

/**
 * Mailer exception class.
 *
 * @package         Webiny\Component\Mailer
 */
class MailerException extends AbstractException
{
    const MESSAGE_INTERFACE = 101;
    const TRANSPORT_INTERFACE = 102;
    const INVALID_CONFIGURATION = 103;

    protected static $messages = [
        101 => 'The message library must implement "\Webiny\Component\Mailer\Bridge\MessageInterface".',
        102 => 'The transport library must implement "\Webiny\Component\Mailer\Bridge\TransportInterface".',
        103 => 'Unable to load the configuration for "%s" mailer.'
    ];
}