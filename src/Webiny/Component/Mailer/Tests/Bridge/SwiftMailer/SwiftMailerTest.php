<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Mailer\Tests\Bridge\SwiftMailer;

use Webiny\Component\Config\ConfigObject;
use Webiny\Component\Mailer\Bridge\MessageInterface;
use Webiny\Component\Mailer\Bridge\SwiftMailer\SwiftMailer;
use Webiny\Component\Mailer\Bridge\TransportInterface;

class SwiftMailerTest extends \PHPUnit_Framework_TestCase
{

    public function testGetTransport()
    {
        $config = [
            'Transport' => [
                'Type' => 'null'
            ]
        ];

        $transport = SwiftMailer::getTransport(new ConfigObject($config));
        $this->assertInstanceOf(TransportInterface::class, $transport);
        $this->assertInstanceOf('\Swift_Transport', $transport->getTransportInstance());
    }

    public function testGetMessage()
    {
        $message = SwiftMailer::getMessage(new ConfigObject([]));
        $this->assertInstanceOf(MessageInterface::class, $message);
    }
}