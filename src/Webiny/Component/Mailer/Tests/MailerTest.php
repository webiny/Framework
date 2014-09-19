<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Mailer\Tests;

use Webiny\Component\Mailer\Mailer;

class MailerTest extends \PHPUnit_Framework_TestCase
{
    const CONFIG = '/ExampleConfig.yaml';


    public function setUp()
    {
        Mailer::setConfig(realpath(__DIR__ . '/' . self::CONFIG));
    }

    public function testConstructor()
    {
        $mailer = new Mailer();
        $this->assertInstanceOf('\Webiny\Component\Mailer\Mailer', $mailer);
    }

    /**
     * @expectedException \Webiny\Component\Mailer\MailerException
     */
    public function testConstructorException()
    {
        $mailer = new Mailer('doesnt exist');
        $this->assertInstanceOf('\Webiny\Component\Mailer\Mailer', $mailer);
    }

    public function testGetMessage()
    {
        $mailer = new Mailer();
        $this->assertInstanceOf('\Webiny\Component\Mailer\MessageInterface', $mailer->getMessage());
    }

    /**
     * @expectedException \Swift_TransportException
     */
    public function testSendWithoutRecipient()
    {
        $mailer = new Mailer();
        $mailer->send($mailer->getMessage());
    }

    public function testSend()
    {
        $mailer = new Mailer();
        $message = $mailer->getMessage();
        $message->setTo(['info@webiny.com' => 'Webiny'])->setBody('Testing')->setSubject('PHPUnit test');

        $result = $mailer->send($message);
        $this->assertNotFalse($result); // this test might fail if sendmail is not configured
    }

    public function testGetConfig()
    {
        $this->assertInstanceOf('\Webiny\Component\Config\ConfigObject', Mailer::getConfig());
    }

    public function testConfigContent()
    {
        $this->assertSame('nikola@tesla.com', Mailer::getConfig()->Default->Sender->Email);
        $this->assertSame('\Webiny\Component\Mailer\Bridge\SwiftMailer\SwiftMailer', Mailer::getConfig()->Bridge);
    }
}