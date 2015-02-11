<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Mailer\Bridge\Mandrill;

use Webiny\Component\Config\ConfigObject;
use Webiny\Component\Mailer\Bridge\MessageInterface;
use Webiny\Component\Mailer\MailerException;
use Webiny\Component\StdLib\StdLibTrait;
use Webiny\Component\Storage\File\LocalFile;
use Webiny\Component\Mailer\Email;

/**
 * Bridge to Mandrill message data
 *
 * @package         Webiny\Component\Mailer\Bridge\Mandrill
 */
class Message implements MessageInterface
{
    use StdLibTrait;

    private $_message = [
        'html'                      => '',
        'subject'                   => '',
        'from_email'                => '',
        'from_name'                 => '',
        'to'                        => [],
        'headers'                   => [],
        'important'                 => false,
        'track_opens'               => null,
        'track_clicks'              => null,
        'auto_text'                 => null,
        'auto_html'                 => null,
        'inline_css'                => null,
        'url_strip_qs'              => null,
        'preserve_recipients'       => null,
        'view_content_link'         => null,
        'bcc_address'               => '',
        'tracking_domain'           => null,
        'signing_domain'            => null,
        'return_path_domain'        => null,
        'merge'                     => true,
        'merge_language'            => 'mailchimp',
        'global_merge_vars'         => [],
        'merge_vars'                => [],
        'tags'                      => [],
        'subaccount'                => null,
        'google_analytics_domains'  => [],
        'google_analytics_campaign' => '',
        'metadata'                  => [],
        'recipient_metadata'        => [],
        'attachments'               => []
    ];

    function __construct(ConfigObject $config)
    {
        // Overwrite default message params with those in config
        foreach ($this->_message as $param => $value) {
            $cParam = $this->str($param)->replace('_', ' ')->caseWordUpper()->replace(' ', '')->val();
            $cValue = $config->get('Message.' . $cParam);
            if ($cValue !== null) {
                if ($cValue instanceof ConfigObject) {
                    $cValue = $cValue->toArray();
                }
                $this->_message[$param] = $cValue;
            }
        }
    }

    public function __invoke(){
        return $this->_message;
    }

    /**
     * Set the message subject.
     *
     * @param string $subject Message subject.
     *
     * @return $this
     */
    function setSubject($subject)
    {
        $this->_message['subject'] = $subject;

        return $this;
    }

    /**
     * Get the current message subject.
     *
     * @return string Message subject.
     */
    function getSubject()
    {
        return $this->_message['subject'];
    }

    /**
     * Specifies the address of the person who the message is from.
     * Can be multiple persons/addresses.
     *
     * @param Email $from
     *
     * @return $this
     */
    function setFrom(Email $from)
    {
        $this->_message['from_name'] = $from->name;
        $this->_message['from_email'] = $from->email;

        return $this;
    }

    /**
     * Returns the person who sent the message.
     *
     * @return array
     */
    function getFrom()
    {
        return new Email($this->_message['from_email'], $this->_message['from_name']);
    }

    /**
     * Specifies the address of the person who physically sent the message.
     * Higher precedence than "from".
     *
     * @param Email $sender
     *
     * @return $this
     */
    function setSender(Email $sender)
    {
        return $this->setFrom($sender);
    }

    /**
     * Return the person who sent the message.
     *
     * @return array
     */
    function getSender()
    {
        return $this->getFrom();
    }

    /**
     * Specifies the addresses of the intended recipients.
     *
     * @param array|Email $to A list of recipients.
     *
     * @return $this
     */
    function setTo($to)
    {
        if($to instanceof Email){
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
    function getTo()
    {
        return $this->_getRecipients('to');
    }

    /**
     * Appends one more recipient to the list.
     *
     * @param Email $email
     *
     * @return $this
     */
    function addTo(Email $email)
    {
        $this->_message ['to'][] = [
            'email' => $email->email,
            'name'  => $email->name,
            'type'  => 'to'
        ];

        return $this;
    }

    /**
     * Specifies the addresses of recipients who will be copied in on the message.
     *
     * @param array|Email $cc
     *
     * @return $this
     */
    function setCc($cc)
    {
        if($cc instanceof Email){
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
    function getCc()
    {
        return $this->_getRecipients('cc');
    }

    /**
     * Appends one more address to the copied list.
     *
     * @param Email $email
     *
     * @return $this
     */
    function addCc(Email $email)
    {
        $this->_message['to'][] = [
            'email' => $email->email,
            'name'  => $email->name,
            'type'  => 'cc'
        ];

        return $this;
    }

    /**
     * Specifies the addresses of recipients who the message will be blind-copied to.
     * Other recipients will not be aware of these copies.
     *
     * @param array|Email $bcc
     *
     * @return $this
     */
    function setBcc($bcc)
    {
        if($bcc instanceof Email){
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
    function getBcc()
    {
        return $this->_getRecipients('bcc');
    }

    /**
     * Appends one more address to the blind-copied list.
     *
     * @param Email $email
     *
     * @return $this
     */
    function addBcc(Email $email)
    {
        $this->_message ['to'][] = [
            'email' => $email->email,
            'name'  => $email->name,
            'type'  => 'bcc'
        ];

        return $this;
    }

    /**
     * Define the reply-to address.
     *
     * @param Email $replyTo
     *
     * @return $this
     */
    function setReplyTo(Email $replyTo)
    {
        $this->addHeader('Reply-To', $replyTo->email);

        return $this;
    }

    /**
     * Returns the reply-to address.
     *
     * @return Email|null
     */
    function getReplyTo()
    {
        return isset($this->_message['headers']['Reply-To']) ? new Email($this->_message['headers']['Reply-To']) : null;
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
    function setBody($content, $type = 'text/html', $charset = 'utf-8')
    {
        $this->_message['html'] = $content;

        return $this;
    }

    /**
     * Returns the body of the message.
     *
     * @return string
     */
    function getBody()
    {
        return $this->_message['html'];
    }

    /**
     * Attach a file to your message.
     *
     * @param LocalFile $file     File instance
     * @param string    $fileName Optional name that will be set for the attachment.
     * @param string    $type     Optional MIME type of the attachment
     *
     * @return $this
     */
    public function addAttachment(LocalFile $file, $fileName = '', $type = 'plain/text')
    {
        $this->_message['attachments'][] = [
            'type'    => $contentType,
            'name'    => $fileName,
            'content' => base64_encode($file->getContents())
        ];

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
    function setReturnPath($returnPath)
    {
        $this->_message['return_path_domain'] = $returnPath;

        return $this;
    }

    /**
     * Returns the defined return-path.
     *
     * @return string
     */
    function getReturnPath()
    {
        return $this->_message['return_path_domain'];
    }

    /**
     * Specifies the format of the message (usually text/plain or text/html).
     *
     * @param string $contentType
     *
     * @return $this
     */
    function setContentType($contentType)
    {
        return $this;
    }

    /**
     * Returns the defined content type of the message.
     *
     * @return string
     */
    function getContentType()
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
    function setContentTransferEncoding($encoding)
    {
        return $this;
    }

    /**
     * Get the defined encoding scheme.
     *
     * @return string
     */
    function getContentTransferEncoding()
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
    function addHeader($name, $value, $params = null)
    {
        $this->_message['headers'][$name] = $value;

        return $this;
    }

    private function _getRecipients($type)
    {
        $recipients = [];
        foreach ($this->_message['to'] as $rcpt) {
            if ($rcpt['type'] == $type) {
                $recipients[] = new Email($rcpt['email'], $rcpt['name']);
            }
        }

        return $recipients;
    }
}