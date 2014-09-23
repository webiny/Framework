<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Router\Tests;

use Webiny\Component\Router\Router;

/**
 * Class CallbackTest
 * @package Webiny\Component\Router\Tests
 * @runTestsInSeparateProcesses
 */
class CallbackTest extends \PHPUnit_Framework_TestCase
{

    const CONFIG = '/CallbackConfig.yaml';

    public function setUp()
    {
        // we need to mock the $_SERVER
        $_SERVER = [
            'SCRIPT_NAME' => '/index.php',
            'SERVER_NAME' => 'www.webiny.com',
        ];

        Router::setConfig(__DIR__ . self::CONFIG);
    }

    public function testInvalidCallback()
    {
        $this->setExpectedException('\Webiny\Component\Router\RouterException');
        $result = Router::getInstance()->match('http://www.webiny.com/blog/tag/php');
        Router::getInstance()->execute($result);
    }

    public function testMissingClass()
    {
        $this->setExpectedException('\Webiny\Component\Router\RouterException');
        $result = Router::getInstance()->match('http://www.webiny.com/no/class');
        Router::getInstance()->execute($result);
    }

    public function testMissingClassMethod()
    {
        $this->setExpectedException('\Webiny\Component\Router\RouterException');
        $result = Router::getInstance()->match('http://www.webiny.com/no/class/method');
        Router::getInstance()->execute($result);
    }

    public function testInstanceCall()
    {
        $result = Router::getInstance()->match('http://www.webiny.com/blog/post/new-php');
        $this->assertEquals('instance-new-php', Router::getInstance()->execute($result));
    }

    public function testStaticCall()
    {
        $result = Router::getInstance()->match('http://www.webiny.com/blog/post/new-php/12');
        $this->assertEquals('static-new-php-12', Router::getInstance()->execute($result));
    }
}