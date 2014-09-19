<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Mailer\Tests\Bridge\SwiftMailer;


use Webiny\Component\ClassLoader\ClassLoader;
use Webiny\Component\Mailer\Bridge\SwiftMailer\Message;

/**
 * Class MessageTest
 *
 * This test tests Bridge\SwiftMailer\Message class which extends \Swift_Message.
 * Methods from \Swift_Message are not tested and are considered to be fully functional and without bugs.
 *
 * @package Webiny\Component\Mailer\Tests\Bridge\SwiftMailer
 */
class MessageTest extends \PHPUnit_Framework_TestCase
{
    const CONFIG = 'ExampleConfig.yaml';

    /**
     * @dataProvider messageProvider
     */
    public function testConstructor($message)
    {
        $this->assertInstanceOf('\Webiny\Component\Mailer\Bridge\SwiftMailer\Message', $message);
    }

    /**
     * @dataProvider messageProvider
     *
     * @param Message $message
     */
    public function testAddAttachment($message)
    {
        $message->addAttachment('../../ExampleConfig.yaml', 'ExampleConfig.yaml', 'text/yaml');
        $children = $message->getChildren();

        $this->assertSame('text/yaml', $children[0]->getContentType());
    }

    /**
     * @dataProvider encoderProvider
     *
     * @param Message $message
     * @param         $encoder
     */
    public function testSetContentTransferEncoding($message, $encoder)
    {
        $message->setContentTransferEncoding($encoder);
        $this->assertSame($encoder, $message->getContentTransferEncoding());
    }

    /**
     * @dataProvider messageProvider
     *
     * @param Message $message
     */
    public function testSetContentTransferEncodingQp($message)
    {
        $message->setContentTransferEncoding('qp');
        $this->assertSame('quoted-printable', $message->getContentTransferEncoding());
    }

    /**
     * @dataProvider messageProvider
     *
     * @param Message $message
     *
     * @expectedException \Webiny\Component\Mailer\Bridge\SwiftMailer\SwiftMailerException
     */
    public function testSetContentTransferEncodingException($message)
    {
        $message->setContentTransferEncoding('doesnt exist');
    }

    /**
     * @dataProvider messageProvider
     *
     * @param Message $message
     *
     */
    public function testAddHeader($message)
    {
        $message->addHeader('test', 'value');
        $this->assertSame('test', $message->getHeaders()->get('test')->getFieldName());
        $this->assertSame('value', $message->getHeaders()->get('test')->getFieldBody());
    }


    public function messageProvider()
    {
        \Webiny\Component\Mailer\Mailer::setConfig(__DIR__ . '/../../' . self::CONFIG);

        return [
            [new Message()]
        ];
    }

    public function encoderProvider()
    {
        \Webiny\Component\Mailer\Mailer::setConfig(__DIR__ . '/../../' . self::CONFIG);

        return [
            [
                new Message(),
                '7bit'
            ],
            [
                new Message(),
                '8bit'
            ],
            [
                new Message(),
                'base64'
            ]
        ];
    }
}