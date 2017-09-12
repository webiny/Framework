<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Mailer\Tests;

use Webiny\Component\Config\ConfigObject;
use Webiny\Component\Mailer\Bridge\SwiftMailer\SwiftMailer;
use Webiny\Component\Mailer\Email;
use Webiny\Component\Mailer\Mailer;
use Webiny\Component\Mailer\MessageInterface;

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
        $this->assertInstanceOf(Mailer::class, $mailer);
    }

    /**
     * @expectedException \Webiny\Component\Mailer\MailerException
     */
    public function testConstructorException()
    {
        $mailer = new Mailer('doesnt exist');
        $this->assertInstanceOf(Mailer::class, $mailer);
    }

    public function testGetMessage()
    {
        $mailer = new Mailer();
        $this->assertInstanceOf(MessageInterface::class, $mailer->getMessage());
    }

    public function testSend()
    {
        $mailer = new Mailer();
        $message = $mailer->getMessage();
        $message->setTo(new Email('info@webiny.com', 'Webiny'))->setBody('Testing')->setSubject('PHPUnit test');

        $result = $mailer->send($message);
        $this->assertNotFalse($result); // this test might fail if sendmail is not configured
    }

    public function testGetConfig()
    {
        $this->assertInstanceOf(ConfigObject::class, Mailer::getConfig());
    }

    public function testConfigContent()
    {
        $this->assertSame('nikola@localhost', Mailer::getConfig()->Default->Sender->Email);
        $this->assertSame('\\' . SwiftMailer::class, Mailer::getConfig()->Bridge->Default);
    }
}