<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Mailer\Bridge\Sendgrid;

use Webiny\Component\Config\ConfigObject;
use Webiny\Component\Mailer\Bridge\MessageInterface;
use Webiny\Component\Mailer\MailerException;
use Webiny\Component\StdLib\StdLibTrait;
use Webiny\Component\StdLib\StdObject\ArrayObject\ArrayObject;
use Webiny\Component\StdLib\StdObject\StdObjectWrapper;
use Webiny\Component\Storage\File\File;
use Webiny\Component\Mailer\Email;

/**
 * Bridge to Sendgrid message data
 *
 * @package         Webiny\Component\Mailer\Bridge\Sendgrid
 */
class Message implements MessageInterface
{
    use StdLibTrait;

    /**
     * @var \SendGrid\Email
     */
    private $message;
    private $to = [];
    private $cc = [];
    private $bcc = [];

    public function __construct(ConfigObject $config = null)
    {
        $this->message = new \SendGrid\Email('', '');
    }

    public function __invoke()
    {
        return $this->message;
    }

    /**
     * Set the message subject.
     *
     * @param string $subject Message subject.
     *
     * @return $this
     */
    public function setSubject($subject)
    {
        $this->message->setSubject($subject);

        return $this;
    }

    /**
     * Get the current message subject.
     *
     * @return string Message subject.
     */
    public function getSubject()
    {
        return $this->message->getSubject();
    }

    /**
     * Specifies the address of the person who the message is from.
     * Can be multiple persons/addresses.
     *
     * @param Email $from
     *
     * @return $this
     */
    public function setFrom(Email $from)
    {
        $this->message->setFrom($from->email);
        $this->message->setFromName($from->name);

        return $this;
    }

    /**
     * Returns the person who sent the message.
     *
     * @return Email
     */
    public function getFrom()
    {
        return new Email($this->message->getFrom(), $this->message->getFromName());
    }

    /**
     * Specifies the address of the person who physically sent the message.
     * Higher precedence than "from".
     *
     * @param Email $sender
     *
     * @return $this
     */
    public function setSender(Email $sender)
    {
        return $this->setFrom($sender);
    }

    /**
     * Return the person who sent the message.
     *
     * @return Email
     */
    public function getSender()
    {
        return $this->getFrom();
    }

    /**
     * Specifies the addresses of the intended recipients.
     *
     * @param array|Email $to A list of recipients.
     *
     * @return $this
     * @throws MailerException
     */
    public function setTo($to)
    {
        if ($to instanceof Email) {
            $to = [$to];
        }

        foreach ($to as $email) {
            if (!$email instanceof Email) {
                throw new MailerException('Email must be an instance of \Webiny\Component\Mailer\Email.');
            }
            $this->addTo($email);
        }

        return $this;
    }

    /**
     * Returns a list of defined recipients.
     *
     * @return array
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * Appends one more recipient to the list.
     *
     * @param Email $email
     *
     * @return $this
     */
    public function addTo(Email $email)
    {
        $this->to[] = $email;
        $this->message->addTo($email->email, $email->name);

        return $this;
    }

    /**
     * Specifies the addresses of recipients who will be copied in on the message.
     *
     * @param array|Email $cc
     *
     * @return $this
     * @throws MailerException
     */
    public function setCc($cc)
    {
        if ($cc instanceof Email) {
            $cc = [$cc];
        }

        foreach ($cc as $email) {
            if (!$email instanceof Email) {
                throw new MailerException('Email must be an instance of \Webiny\Component\Mailer\Email.');
            }
            $this->addCc($email);
        }

        return $this;
    }

    /**
     * Returns a list of addresses to whom the message will be copied to.
     *
     * @return array
     */
    public function getCc()
    {
        return $this->cc;
    }

    /**
     * Appends one more address to the copied list.
     *
     * @param Email $email
     *
     * @return $this
     */
    public function addCc(Email $email)
    {
        $this->message->addCc($email->email);

        return $this;
    }

    /**
     * Specifies the addresses of recipients who the message will be blind-copied to.
     * Other recipients will not be aware of these copies.
     *
     * @param array|Email $bcc
     *
     * @return $this
     * @throws MailerException
     */
    public function setBcc($bcc)
    {
        if ($bcc instanceof Email) {
            $bcc = [$bcc];
        }

        foreach ($bcc as $email) {
            if (!$email instanceof Email) {
                throw new MailerException('Email must be an instance of \Webiny\Component\Mailer\Email.');
            }
            $this->addBcc($email);
        }

        return $this;
    }

    /**
     * Returns a list of defined bcc recipients.
     *
     * @return array
     */
    public function getBcc()
    {
        return $this->bcc;
    }

    /**
     * Appends one more address to the blind-copied list.
     *
     * @param Email $email
     *
     * @return $this
     */
    public function addBcc(Email $email)
    {
        $this->message->addBcc($email->email);

        return $this;
    }

    /**
     * Define the reply-to address.
     *
     * @param Email $replyTo
     *
     * @return $this
     */
    public function setReplyTo(Email $replyTo)
    {
        $this->message->setReplyTo($replyTo->email);

        return $this;
    }

    /**
     * Returns the reply-to address.
     *
     * @return Email|null
     */
    public function getReplyTo()
    {
        $replyTo = $this->message->getReplyTo();

        return $replyTo ? new Email($replyTo) : null;
    }

    /**
     * Set the message body.
     *
     * @param string $content The content of the body.
     * @param string $type    Content type. Default 'text/html'.
     * @param string $charset Content body charset. Default 'utf-8'.
     *
     * @return \Webiny\Component\Mailer\MessageInterface
     */
    public function setBody($content, $type = 'text/html', $charset = 'utf-8')
    {
        $this->message->setHtml($content);

        return $this;
    }

    /**
     * Returns the body of the message.
     *
     * @return string
     */
    public function getBody()
    {
        return $this->message->getHtml();
    }

    /**
     * Attach a file to your message.
     *
     * @param File $file     File instance
     * @param string    $fileName Optional name that will be set for the attachment.
     * @param string    $type     Optional MIME type of the attachment
     *
     * @return $this
     */
    public function addAttachment(File $file, $fileName = '', $type = 'plain/text')
    {
        $this->message->addAttachment($file->getAbsolutePath(), $fileName);

        return $this;
    }

    /**
     * Defines the return path for the email.
     * By default it should be set to the sender.
     *
     * @param string $returnPath
     *
     * @return $this
     */
    public function setReturnPath($returnPath)
    {
        return $this;
    }

    /**
     * Returns the defined return-path.
     *
     * @return string
     */
    public function getReturnPath()
    {
        return null;
    }

    /**
     * Specifies the format of the message (usually text/plain or text/html).
     *
     * @param string $contentType
     *
     * @return $this
     */
    public function setContentType($contentType)
    {
        return $this;
    }

    /**
     * Returns the defined content type of the message.
     *
     * @return string
     */
    public function getContentType()
    {
        return null;
    }

    /**
     * Specifies the encoding scheme in the message.
     *
     * @param string $encoding
     *
     * @return $this
     */
    public function setContentTransferEncoding($encoding)
    {
        return $this;
    }

    /**
     * Get the defined encoding scheme.
     *
     * @return string
     */
    public function getContentTransferEncoding()
    {
        return null;
    }

    /**
     * Adds a header to the message.
     *
     * @param string     $name   Header name.
     * @param string     $value  Header value.
     * @param null|array $params Optional array of parameters.
     *
     * @return $this
     */
    public function addHeader($name, $value, $params = null)
    {
        $this->message->addHeader($name, $value);

        return $this;
    }

    /**
     * Set multiple headers
     *
     * @param array|ArrayObject $headers
     *
     * @return $this
     */
    public function setHeaders($headers)
    {
        $headers = StdObjectWrapper::toArray($headers);
        $this->message->setHeaders($headers);

        return $this;
    }

    /**
     * Get a header from the message.
     *
     * @param string $name Header name.
     *
     * @return mixed
     */
    public function getHeader($name)
    {
        $headers = $this->message->getHeaders();
        return isset($headers[$name]) ? $headers[$name] : null;
    }

    /**
     * Get all headers from the message.
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->message->getHeaders();
    }
}