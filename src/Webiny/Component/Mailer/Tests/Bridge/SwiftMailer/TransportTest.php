<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Mailer\Tests\Bridge\SwiftMailer;

use Webiny\Component\Config\ConfigObject;
use Webiny\Component\Mailer\Bridge\SwiftMailer\Transport;

class TransportTest extends \PHPUnit_Framework_TestCase
{

    public function testSmtp()
    {
        $config = [
            'Transport' => [
                'Type' => 'smtp'
            ]
        ];

        $transport = new Transport(new ConfigObject($config));
        $this->assertInstanceOf('\Swift_SmtpTransport', $transport->getTransportInstance());
    }

    public function testMail()
    {
        $config = [
            'Transport' => [
                'Type' => 'mail'
            ]
        ];

        $transport = new Transport(new ConfigObject($config));
        $this->assertInstanceOf('\Swift_MailTransport', $transport->getTransportInstance());
    }

    public function testNull()
    {
        $config = [
            'Transport' => [
                'Type' => 'null'
            ]
        ];

        $transport = new Transport(new ConfigObject($config));
        $this->assertInstanceOf('\Swift_NullTransport', $transport->getTransportInstance());
    }

    public function testSendmail()
    {
        $config = [
            'Transport' => [
                'Type' => 'sendmail'
            ]
        ];

        $transport = new Transport(new ConfigObject($config));
        $this->assertInstanceOf('\Swift_SendmailTransport', $transport->getTransportInstance());
    }

    /**
     * @expectedException \Webiny\Component\Mailer\Bridge\SwiftMailer\SwiftMailerException
     */
    public function testTransportException()
    {
        $config = [
            'Transport' => [
                'Type' => 'doesnt exist'
            ]
        ];

        $transport = new Transport(new ConfigObject($config));
    }
}