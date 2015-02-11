<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Mailer\Tests\Bridge;


use Webiny\Component\Mailer\Bridge\Loader;
use Webiny\Component\Mailer\Mailer;

class LoaderTest extends \PHPUnit_Framework_TestCase
{
    const CONFIG = '/../ExampleConfig.yaml';


    public function setUp()
    {
        Mailer::setConfig(realpath(__DIR__ . '/' . self::CONFIG));
    }

    public function testGetMessage()
    {
        $this->assertInstanceOf('\Webiny\Component\Mailer\Bridge\MessageInterface', Loader::getMessage('Default'));
    }

    public function testGetTransport()
    {
        $this->assertInstanceOf('\Webiny\Component\Mailer\Bridge\TransportInterface', Loader::getTransport('Default'));
    }
}