<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Mailer;

use Webiny\Component\Config\ConfigObject;

/**
 * The TransportInterface defines the methods required by the transport layer.
 * The transport layer is provided by Mailer bridge.
 *
 * @package         Webiny\Component\Mailer
 */
interface TransportInterface
{
    /**
     * Base constructor.
     * In the base constructor the bridge gets the mailer configuration.
     *
     * @param ConfigObject $config The base configuration.
     */
    function __construct($config);

    /**
     * Sends the message.
     *
     * @param MessageInterface $message  Message you want to send.
     * @param null|array       $failures To this array failed addresses will be stored.
     *
     * @return bool|int Number of success sends, or bool FALSE if sending failed.
     */
    function send(MessageInterface $message, &$failures = null);

    /**
     * Decorators are arrays that contain keys and values. The message body and subject will be scanned for the keys,
     * and, where found, the key will be replaced with the value.
     *
     * @param array $replacements Array [email=> [key1=>value1, key2=>value2], email2=>[...]].
     *
     * @return $this
     */
    function setDecorators(array $replacements);
}