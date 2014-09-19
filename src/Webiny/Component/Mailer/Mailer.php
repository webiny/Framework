<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Mailer;

use Webiny\Component\Mailer\Bridge\Loader;
use Webiny\Component\StdLib\ComponentTrait;
use Webiny\Component\StdLib\StdLibTrait;

/**
 * This is the Mailer component class.
 *
 * This class provides access to mail Transport and mail Message object.
 * Use the getMessage to create an email message, and then use the send method to send it using the Transport object.
 *
 * @package         Webiny\Component\Mailer
 */
class Mailer
{
    use StdLibTrait, ComponentTrait;

    /**
     * @var string Name of the mailer we are currently using.
     */
    private $_mailerName;

    /**
     * @var TransportInterface
     */
    private $_transport;

    /**
     * Base constructor.
     *
     * @param string $mailer Key of the mailer configuration.
     *
     * @throws MailerException
     */
    public function __construct($mailer = 'Default')
    {
        $this->_mailerName = $mailer;
        $this->_transport = Loader::getTransport($this->_getMailerConfig());
    }

    /**
     * Creates a new message.
     *
     * @return MessageInterface
     */
    public function getMessage()
    {
        return Loader::getMessage($this->_getMailerConfig());
    }

    /**
     * Sends the message.
     *
     * @param MessageInterface $message  Message you want to send.
     * @param null|array       $failures To this array failed addresses will be stored.
     *
     * @return bool|int Number of success sends, or bool FALSE if sending failed.
     */
    public function send(MessageInterface $message, &$failures = null)
    {
        return $this->_transport->send($message, $failures);
    }

    /**
     * Decorators are arrays that contain keys and values. The message body and subject will be scanned for the keys,
     * and, where found, the key will be replaced with the value.
     *
     * @param array $replacements Array [key1=>value1, key2=>value2].
     *
     * @return $this
     */
    public function setDecorators(array $replacements)
    {
        $this->_transport->setDecorators($replacements);

        return $this;
    }

    /**
     * Returns the config for current mailer.
     *
     * @return mixed|\Webiny\Component\Config\ConfigObject
     * @throws MailerException
     */
    private function _getMailerConfig()
    {
        $config = $this->getConfig()->get($this->_mailerName, false);

        if (!$config) {
            throw new MailerException('Unable to load the configuration for "' . $this->_mailerName . '" mailer.');
        }

        return $config;
    }
}