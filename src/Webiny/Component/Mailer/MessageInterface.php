<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Mailer;

/**
 * This interface defines the structure of the message object.
 * The object itself is provided by the Mailer bridge.
 *
 * @package         Webiny\Component\Mailer
 */

interface MessageInterface
{
    /**
     * Set the message subject.
     *
     * @param string $subject Message subject.
     *
     * @return $this
     */
    function setSubject($subject);

    /**
     * Get the current message subject.
     *
     * @return string Message subject.
     */
    function getSubject();

    /**
     * Specifies the address of the person who the message is from.
     * Can be multiple persons/addresses.
     *
     * @param string|array $from From name and email: ['from@domain.org' => 'From Name']
     *
     * @return $this
     */
    function setFrom($from);

    /**
     * Returns the person who sent the message.
     *
     * @return array
     */
    function getFrom();

    /**
     * Specifies the address of the person who physically sent the message.
     * Higher precedence than "from".
     *
     * @param string|array $sender Sender name and email: ['sender@domain.org' => 'Sender Name']
     *
     * @return $this
     */
    function setSender($sender);

    /**
     * Return the person who sent the message.
     *
     * @return array
     */
    function getSender();

    /**
     * Specifies the addresses of the intended recipients.
     *
     * @param string|array $to A list of recipients.
     *
     * @return $this
     */
    function setTo($to);

    /**
     * Returns a list of defined recipients.
     *
     * @return array
     */
    function getTo();

    /**
     * Appends one more recipient to the list.
     *
     * @param string $email
     * @param string $name
     *
     * @return $this
     */
    function addTo($email, $name = '');

    /**
     * Specifies the addresses of recipients who will be copied in on the message.
     *
     * @param string $cc
     *
     * @return $this
     */
    function setCc($cc);

    /**
     * Returns a list of addresses to whom the message will be copied to.
     *
     * @return array
     */
    function getCc();

    /**
     * Appends one more address to the copied list.
     *
     * @param string $email
     * @param string $name
     *
     * @return $this
     */
    function addCc($email, $name = '');

    /**
     * Specifies the addresses of recipients who the message will be blind-copied to.
     * Other recipients will not be aware of these copies.
     *
     * @param string|array $bcc
     *
     * @return $this
     */
    function setBcc($bcc);

    /**
     * Returns a list of defined bcc recipients.
     *
     * @return array
     */
    function getBcc();

    /**
     * Appends one more address to the blind-copied list.
     *
     * @param string $email
     * @param string $name
     *
     * @return $this
     */
    function addBcc($email, $name = '');

    /**
     * Define the reply-to address.
     *
     * @param string|array $replyTo
     *
     * @return $this
     */
    function setReplyTo($replyTo);

    /**
     * Returns the reply-to address.
     *
     * @return string|array
     */
    function getReplyTo();

    /**
     * Set the message body.
     *
     * @param string $content The content of the body.
     * @param string $type    Content type. Default 'text/html'.
     * @param string $charset Content body charset. Default 'utf-8'.
     *
     * @return MessageInterface
     */
    function setBody($content, $type = 'text/html', $charset = 'utf-8');

    /**
     * Returns the body of the message.
     *
     * @return string
     */
    function getBody();

    /**
     * Attach a file to your message.
     *
     * @param string $pathToFile Absolute path to the file.
     * @param string $fileName   Optional name that will be set for the attachment.
     *
     * @return $this
     */
    function addAttachment($pathToFile, $fileName = '');

    /**
     * Defines the return path for the email.
     * By default it should be set to the sender.
     *
     * @param string $returnPath
     *
     * @return $this
     */
    function setReturnPath($returnPath);

    /**
     * Returns the defined return-path.
     *
     * @return string
     */
    function getReturnPath();

    /**
     * Specifies the format of the message (usually text/plain or text/html).
     *
     * @param string $contentType
     *
     * @return $this
     */
    function setContentType($contentType);

    /**
     * Returns the defined content type of the message.
     *
     * @return string
     */
    function getContentType();

    /**
     * Specifies the encoding scheme in the message.
     *
     * @param string $encoding
     *
     * @return $this
     */
    function setContentTransferEncoding($encoding);

    /**
     * Get the defined encoding scheme.
     *
     * @return string
     */
    function getContentTransferEncoding();

    /**
     * Adds a header to the message.
     *
     * @param string     $name   Header name.
     * @param string     $value  Header value.
     * @param null|array $params Optional array of parameters.
     *
     * @return $this
     */
    function addHeader($name, $value, $params = null);
}