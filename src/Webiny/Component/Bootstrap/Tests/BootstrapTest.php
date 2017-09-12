<?php

namespace Webiny\Component\Bootstrap\Tests;

use Webiny\Component\Bootstrap\Bootstrap;
use Webiny\Component\Bootstrap\Environment;
use Webiny\Component\Http\Request;

/**
 * Class BootstrapTest
 * @package Webiny\Component\Bootstrap\Tests
 */
class BootstrapTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Request::getInstance()->setCurrentUrl('http://www.myapp.com/');
    }

    /**
     * @throws \Exception
     * @throws \Webiny\Component\Bootstrap\BootstrapException
     * @expectedExceptionMessage Unable to read app config file
     * @expectedException \Webiny\Component\Bootstrap\BootstrapException
     */
    public function testRunApplicationException()
    {
        Bootstrap::getInstance()->runApplication();
    }

    public function testInitializeEnvironment()
    {
        $b = Bootstrap::getInstance();
        $b->initializeEnvironment(__DIR__ . '/DemoApp/');
        $this->assertInstanceOf(Environment::class, $b->getEnvironment());
    }

    public function testInitializeRouter()
    {
        $b = Bootstrap::getInstance();
        $b->initializeEnvironment(__DIR__ . '/DemoApp/');
        $b->initializeRouter();
    }


    public function testGetEnvironment()
    {
        $env = Bootstrap::getInstance()->getEnvironment();
        $this->assertInstanceOf(Environment::class, $env);
    }
}