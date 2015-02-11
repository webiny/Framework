<?php

namespace Webiny\Component\Bootstrap\Tests;

class RouterTest extends \PHPUnit_Framework_TestCase
{
    public function testInitializeRouter()
    {
        $dispatcher = \Webiny\Component\Bootstrap\Router::getInstance()->initializeRouter('http://www.myapp.com/');
        $cName = $dispatcher->getClassName();

        $this->assertSame('\Webiny\Component\Bootstrap\Tests\DemoApp\Modules\MyModule\Controllers\MyCtrl', $cName);
    }

    /**
     * @expectedExceptionMessage Unable to route this request.
     * @expectedException \Webiny\Component\Bootstrap\BootstrapException
     */
    public function testInitializeRouterException()
    {
        \Webiny\Component\Bootstrap\Router::getInstance()->initializeRouter('http://www.myapp.com/test/');
    }

    public function testMvcRouterLowerCase()
    {
        $dispatcher = \Webiny\Component\Bootstrap\Router::getInstance()->mvcRouter('/my-module/my-ctrl/my-act/');
        $cName = $dispatcher->getClassName();

        $this->assertSame('\Webiny\Component\Bootstrap\Tests\DemoApp\Modules\MyModule\Controllers\MyCtrl', $cName);
    }

    public function testMvcRouterCamelCase()
    {
        $dispatcher = \Webiny\Component\Bootstrap\Router::getInstance()->mvcRouter('/MyModule/MyCtrl/MyAct/');
        $cName = $dispatcher->getClassName();

        $this->assertSame('\Webiny\Component\Bootstrap\Tests\DemoApp\Modules\MyModule\Controllers\MyCtrl', $cName);
    }

    /**
     * @expectedExceptionMessage The provided callback class
     * @expectedException \Webiny\Component\Bootstrap\BootstrapException
     */
    public function testMvcRouterException()
    {
        \Webiny\Component\Bootstrap\Router::getInstance()->mvcRouter('/invalid-module/my-ctrl/my-act/');
    }


}