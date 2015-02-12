<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Mailer;

use Webiny\Component\StdLib\StdObject\ArrayObject\ArrayObject;
use Webiny\Component\Storage\File\LocalFile;

/**
 * This interface defines the structure of the message object.
 * The object itself is provided by the Mailer bridge.
 *
 * @package         Webiny\Component\Mailer
 */
interface MessageInterface
{
    /**
     * @return mixed Message formatted for the mailer being implemented
     * Example: array for Mandrill, \Swift_Message for SwiftMailer, etc.
     */
    public function __invoke();

    /**
     * Set the message subject.
     *
     * @param string $subject Message subject.
     *
     * @return $this
     */
    public function setSubject($subject);

    /**
     * Get the current message subject.
     *
     * @return string Message subject.
     */
    public function getSubject();

    /**
     * Specifies the address of the person who the message is from.
     * Can be multiple persons/addresses.
     *
     * @param Email $from
     *
     * @return $this
     */
    public function setFrom(Email $from);

    /**
     * Returns the person who sent the message.
     *
     * @return Email
     */
    public function getFrom();

    /**
     * Specifies the address of the person who physically sent the message.
     * Higher precedence than "from".
     *
     * @param Email $sender
     *
     * @return $this
     */
    public function setSender(Email $sender);

    /**
     * Return the person who sent the message.
     *
     * @return Email
     */
    public function getSender();

    /**
     * Specifies the emails of the intended recipients.
     *
     * @param array|Email $to A list of recipients (instances of Email).
     *
     * @return $this
     */
    public function setTo($to);

    /**
     * Returns a list of defined recipients.
     *
     * @return array
     */
    public function getTo();

    /**
     * Appends one more recipient to the list.
     *
     * @param Email $email
     *
     * @return $this
     */
    public function addTo(Email $email);

    /**
     * Specifies the addresses of recipients who will be copied in on the message.
     *
     * @param array|Email $cc
     *
     * @return $this
     */
    public function setCc($cc);

    /**
     * Returns a list of addresses to whom the message will be copied to.
     *
     * @return array
     */
    public function getCc();

    /**
     * Appends one more address to the copied list.
     *
     * @param Email $email
     *
     * @return $this
     */
    public function addCc(Email $email);

    /**
     * Specifies the addresses of recipients who the message will be blind-copied to.
     * Other recipients will not be aware of these copies.
     *
     * @param array|Email $bcc
     *
     * @return $this
     */
    public function setBcc($bcc);

    /**
     * Returns a list of defined bcc recipients.
     *
     * @return array
     */
    public function getBcc();

    /**
     * Appends one more address to the blind-copied list.
     *
     * @param Email $email
     *
     * @return $this
     */
    public function addBcc(Email $email);

    /**
     * Define the reply-to address.
     *
     * @param Email $replyTo
     *
     * @return $this
     */
    public function setReplyTo(Email $replyTo);

    /**
     * Returns the reply-to address.
     *
     * @return Email
     */
    public function getReplyTo();

    /**
     * Set the message body.
     *
     * @param string $content The content of the body.
     * @param string $type    Content type. Default 'text/html'.
     * @param string $charset Content body charset. Default 'utf-8'.
     *
     * @return MessageInterface
     */
    public function setBody($content, $type = 'text/html', $charset = 'utf-8');

    /**
     * Returns the body of the message.
     *
     * @return string
     */
    public function getBody();

    /**
     * Attach a file to your message.
     *
     * @param LocalFile $file     File instance
     * @param string    $fileName Optional name that will be set for the attachment.
     * @param string    $type     Optional MIME type of the attachment
     *
     * @return $this
     */
    public function addAttachment(LocalFile $file, $fileName = '', $type = 'plain/text');

    /**
     * Defines the return path for the email.
     * By default it should be set to the sender.
     *
     * @param string $returnPath
     *
     * @return $this
     */
    public function setReturnPath($returnPath);

    /**
     * Returns the defined return-path.
     *
     * @return string
     */
    public function getReturnPath();

    /**
     * Specifies the format of the message (usually text/plain or text/html).
     *
     * @param string $contentType
     *
     * @return $this
     */
    public function setContentType($contentType);

    /**
     * Returns the defined content type of the message.
     *
     * @return string
     */
    public function getContentType();

    /**
     * Specifies the encoding scheme in the message.
     *
     * @param string $encoding
     *
     * @return $this
     */
    public function setContentTransferEncoding($encoding);

    /**
     * Get the defined encoding scheme.
     *
     * @return string
     */
    public function getContentTransferEncoding();

    /**
     * Set multiple headers
     *
     * @param array|ArrayObject $headers
     *
     * @return $this
     */
    public function setHeaders($headers);

    /**
     * Adds a header to the message.
     *
     * @param string     $name   Header name.
     * @param string     $value  Header value.
     * @param null|array $params Optional array of parameters.
     *
     * @return $this
     */
    public function addHeader($name, $value, $params = null);

    /**
     * Get a header from the message.
     *
     * @param string     $name   Header name.
     *
     * @return mixed
     */
    public function getHeader($name);

    /**
     * Get all headers from the message.
     *
     * @return array
     */
    public function getHeaders();
}