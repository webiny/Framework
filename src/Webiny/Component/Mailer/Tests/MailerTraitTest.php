<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Mailer\Tests;

use Webiny\Component\Mailer\Mailer;
use Webiny\Component\Mailer\MailerTrait;

class MailerTraitTest extends \PHPUnit_Framework_TestCase
{
    use MailerTrait;

    const CONFIG = '/ExampleConfig.yaml';


    public function setUp()
    {
        Mailer::setConfig(realpath(__DIR__ . '/' . self::CONFIG));
    }

    public function testMailerTrait()
    {
        $this->assertInstanceOf(Mailer::class, $this->mailer('Default'));
    }

    /**
     * @expectedException \Webiny\Component\Mailer\MailerException
     */
    public function testMailerTraitException()
    {
        $this->mailer('doesnt exist');
    }
}