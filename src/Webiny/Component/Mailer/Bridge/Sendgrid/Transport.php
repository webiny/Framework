<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Mailer\Bridge\Sendgrid;

use SendGrid\Email;
use Webiny\Component\Mailer\Bridge\TransportInterface;
use Webiny\Component\Config\ConfigObject;
use Webiny\Component\Mailer\MessageInterface;

/**
 * Transport class bridges the Mailers' transport layer to Sendgrid class.
 *
 * @package         Webiny\Component\Mailer\Bridge\Sendgrid
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
     * @throws SendgridException
     */
    public function __construct($config)
    {
        $this->_config = $config;
        $this->_disableDelivery = $config->get('DisableDelivery', false);

        $apiKey = $config->get('ApiKey', false);
        $apiUser = $config->get('ApiUser', false);
        if (!$apiKey) {
            throw new SendgridException('`ApiKey` was not found in the mailer configuration!');
        }
        $this->_mailer = new \SendGrid($apiUser, $apiKey);
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

        $data = [];
        $ld = $this->_config->get('Decorators.Wrapper.0', '');
        $rd = $this->_config->get('Decorators.Wrapper.1', '');
        foreach ($this->_decorators as $email => $vars) {
            foreach ($vars as $name => $content) {
                $data[$ld . $name . $rd][] = $content;
            }
        }

        $msg = $message();
        /* @var $msg Email */
        $msg->setSubstitutions($data);

        $res = $this->_mailer->send($message());

        if ($res->message == 'success') {
            return true;
        }

        return false;
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