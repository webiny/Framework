<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Mailer\Bridge\Mandrill;

use Webiny\Component\Mailer\Bridge\TransportInterface;
use Webiny\Component\Config\ConfigObject;
use Webiny\Component\Mailer\MessageInterface;

/**
 * Transport class bridges the Mailers' transport layer to Mandrill class.
 *
 * @package         Webiny\Component\Mailer\Bridge\Mandrill
 */
class Transport implements TransportInterface
{

    private $_mailer = null;
    private $_disableDelivery = false;
    private $_decorators = [];
    private $_config;

    /**
     * Base constructor.
     * In the base constructor the bridge gets the mailer configuration.
     *
     * @param ConfigObject $config The base configuration.
     *
     * @throws MandrillException
     */
    public function __construct($config)
    {
        $this->_config = $config;
        $this->_disableDelivery = $config->get('DisableDelivery', false);

        $apiKey = $config->get('ApiKey', false);
        if (!$apiKey) {
            throw new MandrillException('`ApiKey` was not found in the mailer configuration!');
        }
        $this->_mailer = new \Mandrill($apiKey);
    }


    /**
     * Sends the message.
     *
     * @param MessageInterface $message  Message you want to send.
     * @param array|null       $failures To this array failed addresses will be stored.
     *
     * @return bool|int Number of success sends, or bool FALSE if sending failed.
     */
    public function send(MessageInterface $message, &$failures = null)
    {
        if ($this->_disableDelivery) {
            return true;
        }

        $template = $message->getBody();
        $message = $message();
        foreach ($this->_decorators as $email => $vars) {
            $data = [];
            foreach ($vars as $name => $content) {
                $data[] = [
                    'name'    => $name,
                    'content' => $content
                ];
            }
            $message['merge_vars'][] = [
                'rcpt' => $email,
                'vars' => $data
            ];
        }

        if($this->_config->get('Mode', 'template') == 'template'){
            unset($message['html']);
            $res = $this->_mailer->messages->sendTemplate($template, [], $message);
        } else {
            $res = $this->_mailer->messages->send($message);
        }


        // Count successful sends and store failed emails
        $success = 0;
        foreach ($res as $report) {
            if ($report['status'] == 'rejected' || $report['status'] == 'invalid') {
                if ($failures) {
                    $failures[] = $report['email'];
                }
                continue;
            }
            $success++;
        }

        return $success;
    }

    /**
     * Decorators are arrays that contain keys and values. The message body and subject will be scanned for the keys,
     * and, where found, the key will be replaced with the value.
     *
     * @param array $replacements Array [email=> [key1=>value1, key2=>value2], email2=>[...]].
     *
     * @return $this
     */
    public function setDecorators(array $replacements)
    {
        $this->_decorators = $replacements;

        return $this;
    }
}