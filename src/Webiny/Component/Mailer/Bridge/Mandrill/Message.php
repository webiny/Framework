<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Mailer\Bridge\Mandrill;

use Webiny\Component\Config\ConfigObject;
use Webiny\Component\Mailer\Bridge\MessageInterface;
use Webiny\Component\StdLib\StdLibTrait;

/**
 * Bridge to Mandrill message data
 *
 * @package         Webiny\Component\Mailer\Bridge\Mandrill
 */
class Message implements MessageInterface
{
    use StdLibTrait;

    private $_template;
    private $_message = [
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
        'attachments'               => [],
        'images'                    => []
    ];

    function __construct(ConfigObject $config)
    {
        // Overwrite default message params with those in config
        foreach ($this->_message as $param => $value) {
            $cParam = $this->str($param)->replace('_', ' ')->caseWordUpper()->replace(' ', '')->val();
            $cValue = $config->get('Message.' . $cParam);
            if ($cValue) {
                if($cValue instanceof ConfigObject){
                    $cValue = $cValue->toArray();
                }
                $this->_message[$param] = $cValue;
            }
        }
    }

    function toArray()
    {
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
     * @param string|array $from From name and email: ['from@domain.org' => 'From Name']
     *
     * @return $this
     */
    function setFrom($from)
    {
        if (is_string($from)) {
            $fromName = $fromEmail = $from;
        } else {
            $fromEmail = array_keys($from)[0];
            $fromName = $from[$fromEmail];
        }

        $this->_message['from_name'] = $fromName;
        $this->_message['from_email'] = $fromEmail;

        return $this;
    }

    /**
     * Returns the person who sent the message.
     *
     * @return array
     */
    function getFrom()
    {
        return [$this->_message['from_email'] => $this->_message['from_name']];
    }

    /**
     * Specifies the address of the person who physically sent the message.
     * Higher precedence than "from".
     *
     * @param string|array $sender Sender name and email: ['sender@domain.org' => 'Sender Name']
     *
     * @return $this
     */
    function setSender($sender)
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
     * @param string|array $to A list of recipients.
     *
     * @return $this
     */
    function setTo($to)
    {
        if (is_string($to)) {
            $to = [$to => $to];
        }

        foreach ($to as $email => $name) {
            $this->addTo($email, $name);
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
     * @param string $email
     * @param string $name
     *
     * @return $this
     */
    function addTo($email, $name = '')
    {
        $this->_message ['to'][] = [
            'email' => $email,
            'name'  => $name,
            'type'  => 'to'
        ];

        return $this;
    }

    /**
     * Specifies the addresses of recipients who will be copied in on the message.
     *
     * @param string $cc
     *
     * @return $this
     */
    function setCc($cc)
    {
        if (is_string($cc)) {
            $cc = [$cc => $cc];
        }

        foreach ($cc as $email => $name) {
            $this->addCc($email, $name);
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
     * @param string $email
     * @param string $name
     *
     * @return $this
     */
    function addCc($email, $name = '')
    {
        $this->_message ['to'][] = [
            'email' => $email,
            'name'  => $name,
            'type'  => 'cc'
        ];

        return $this;
    }

    /**
     * Specifies the addresses of recipients who the message will be blind-copied to.
     * Other recipients will not be aware of these copies.
     *
     * @param string|array $bcc
     *
     * @return $this
     */
    function setBcc($bcc)
    {
        if (is_string($bcc)) {
            $bcc = [$bcc => $bcc];
        }

        foreach ($bcc as $email => $name) {
            $this->addBcc($email, $name);
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
     * @param string $email
     * @param string $name
     *
     * @return $this
     */
    function addBcc($email, $name = '')
    {
        $this->_message ['to'][] = [
            'email' => $email,
            'name'  => $name,
            'type'  => 'bcc'
        ];

        return $this;
    }

    /**
     * Define the reply-to address.
     *
     * @param string|array $replyTo
     *
     * @return $this
     */
    function setReplyTo($replyTo)
    {
        if (is_string($replyTo)) {
            $this->addHeader('Reply-To', $replyTo);
        } else {
            $this->addHeader('Reply-To', array_keys($replyTo));
        }

        return $this;
    }

    /**
     * Returns the reply-to address.
     *
     * @return string|array
     */
    function getReplyTo()
    {
        return isset($this->_message['headers']['Reply-To']) ? $this->_message['headers']['Reply-To'] : null;
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
        $this->_template = $content;

        return $this;
    }

    /**
     * Returns the body of the message.
     *
     * @return string
     */
    function getBody()
    {
        return $this->_template;
    }

    /**
     * Attach a file to your message.
     *
     * @param string $pathToFile Absolute path to the file.
     * @param string $fileName   Optional name that will be set for the attachment.
     *
     * @return $this
     */
    function addAttachment($pathToFile, $fileName = '')
    {
        $this->_message['attachments'][] = [
            'type'    => 'text/plain',
            'name'    => 'myfile.txt',
            'content' => 'ZXhhbXBsZSBmaWxl'
            // base64 encoded string
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
                $recipients[] = $rcpt;
            }
        }

        return $recipients;
    }
}