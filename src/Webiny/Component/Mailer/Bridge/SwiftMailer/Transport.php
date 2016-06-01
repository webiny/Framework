<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Mailer\Bridge\SwiftMailer;

use Webiny\Component\Mailer\Bridge\MailerException;
use Webiny\Component\Mailer\Bridge\TransportInterface;
use Webiny\Component\Config\ConfigObject;
use Webiny\Component\Mailer\MessageInterface;

/**
 * Transport class bridges the Mailers' transport layer to SwiftMailer Transport class.
 *
 * @package         Webiny\Component\Mailer\Bridge\SwiftMailer
 */
class Transport implements TransportInterface
{

    /**
     * @var null|\Swift_Mailer
     */
    private $mailer = null;
    /**
     * @var null|\Swift_Plugins_Loggers_ArrayLogger
     */
    private $logger = null;
    private $config;

    /**
     * Base constructor.
     * In the base constructor the bridge gets the mailer configuration.
     *
     * @param ConfigObject $config The base configuration.
     *
     * @throws SwiftMailerException
     */
    public function __construct($config)
    {
        $this->config = $config;
        $transportType = strtolower($config->get('Transport.Type', 'mail'));
        $disableDelivery = $config->get('DisableDelivery', false);
        if ($disableDelivery) {
            $transportType = 'null';
        }

        // create Transport instance
        switch ($transportType) {
            case 'smtp':
                $transport = \Swift_SmtpTransport::newInstance($config->get('Transport.Host', 'localhost'),
                    $config->get('Transport.Port', 25), $config->get('Transport.AuthMode', null));
                $transport->setUsername($config->get('Transport.Username', ''));
                $transport->setPassword($config->get('Transport.Password', ''));
                $transport->setEncryption($config->get('Transport.Encryption', null));

                break;

            case 'mail':
                $transport = \Swift_MailTransport::newInstance();
                break;

            case 'sendmail':
                $transport = \Swift_SendmailTransport::newInstance($config->get('Transport.Command',
                    '/usr/sbin/sendmail -bs'));
                break;

            case 'null':
                $transport = \Swift_NullTransport::newInstance();
                break;

            default:
                throw new SwiftMailerException('Invalid transport.type provided.
												Supported types are [smtp, mail, sendmail, null].');
                break;
        }

        // create Mailer instance
        $this->mailer = \Swift_Mailer::newInstance($transport);

        // register plugins
        $this->registerPlugins($config);
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
        return $this->mailer->send($message(), $failures);
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
        $wrapper = $this->config->get('Decorators.Wrapper');
        if ($wrapper) {
            foreach ($replacements as $email => $vars) {
                $decorators = [];
                foreach ($vars as $key => $value) {
                    $key = $wrapper[0] . $key . $wrapper[1];
                    $decorators[$key] = $value;
                }
                $replacements[$email] = $decorators;
            }
        }

        $decoratorPlugin = new \Swift_Plugins_DecoratorPlugin($replacements);
        $this->mailer->registerPlugin($decoratorPlugin);

        return $this;
    }

    /**
     * Returns the debug log.
     * Note that SwiftMailer requires you set 'Debug: true' in your config.
     * @return array
     * @throws MailerException
     */
    public function getDebugLog()
    {
        if (empty($this->logger)) {
            throw new MailerException('Logger is not turned on in the config.');
        }

        return $this->logger->dump();
    }

    /**
     * Returns the current Swift_Transport instance.
     *
     * @return \Swift_Transport
     */
    public function getTransportInstance()
    {
        return $this->mailer->getTransport();
    }

    /**
     * Registers SwiftMailer plugins based on the provided $config.
     *
     * @param ConfigObject $config
     */
    private function registerPlugins(ConfigObject $config)
    {
        // antiflood
        if ($config->get('AntiFlood', false)) {
            $antiflood = new \Swift_Plugins_AntiFloodPlugin($config->get('AntiFlood.Threshold', 99),
                $config->get('AntiFlood.Sleep', 1));
            $this->mailer->registerPlugin($antiflood);
        }

        // array logger
        if ($config->get('Debug', false)) {
            $this->logger = new \Swift_Plugins_Loggers_ArrayLogger();
            $this->mailer->registerPlugin(new \Swift_Plugins_LoggerPlugin($this->logger));
        }
    }
}