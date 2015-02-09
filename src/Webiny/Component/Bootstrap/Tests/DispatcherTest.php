<?php

namespace Webiny\Component\Bootstrap\Tests;


use Webiny\Component\Bootstrap\Dispatcher;

class DispatcherTest extends \PHPUnit_Framework_TestCase
{

    public function testMvcDispatcher()
    {
        $d = Dispatcher::mvcDispatcher('MyModule', 'MyCtrl', 'MyAct', []);
        $this->assertInstanceOf('\Webiny\Component\Bootstrap\Dispatcher', $d);
    }

    /**
     * @expectedExceptionMessage The provided callback class
     * @expectedException \Webiny\Component\Bootstrap\BootstrapException
     */
    public function testMvcDispatcherException()
    {
        Dispatcher::mvcDispatcher('DoesntExist', 'MyCtrl', 'MyAct', []);
    }

    public function testCustomDispatcher()
    {
        $class = '\Webiny\Component\Bootstrap\Tests\DemoApp\Modules\MyModule\Controllers\MyCtrl';
        $d = Dispatcher::customDispatcher($class, 'MyActAction', []);
        $this->assertInstanceOf('\Webiny\Component\Bootstrap\Dispatcher', $d);
    }

    /**
     * @expectedExceptionMessage The provided callback class
     * @expectedException \Webiny\Component\Bootstrap\BootstrapException
     */
    public function testCustomDispatcherException()
    {
        $class = 'DoesntExist';
        Dispatcher::customDispatcher($class, 'MyActAction', []);
    }

    public function testSetGetModule()
    {
        $d = Dispatcher::mvcDispatcher('MyModule', 'MyCtrl', 'MyAct', []);
        $this->assertSame('MyModule', $d->getModule());

        $d->setModule('TestModule');

        $this->assertSame('TestModule', $d->getModule());
    }

    public function testSetGetController()
    {
        $d = Dispatcher::mvcDispatcher('MyModule', 'MyCtrl', 'MyAct', []);
        $this->assertSame('MyCtrl', $d->getController());

        $d->setController('TestCtrl');

        $this->assertSame('TestCtrl', $d->getController());
    }

    public function testSetGetAction()
    {
        $d = Dispatcher::mvcDispatcher('MyModule', 'MyCtrl', 'MyAct', []);
        $this->assertSame('MyAct', $d->getAction());

        $d->setAction('TestCtrl');

        $this->assertSame('TestCtrl', $d->getAction());
    }

    public function testSetGetParams()
    {
        $d = Dispatcher::mvcDispatcher('MyModule', 'MyCtrl', 'MyAct', ['test1', 'test2']);
        $this->assertSame(['test1', 'test2'], $d->getParams());

        $d->setParams(['test3', 'test4']);

        $this->assertSame(['test3', 'test4'], $d->getParams());
    }

    public function testGetClassName()
    {
        $d = Dispatcher::mvcDispatcher('MyModule', 'MyCtrl', 'MyAct', ['test1', 'test2']);
        $class = '\Webiny\Component\Bootstrap\Tests\DemoApp\Modules\MyModule\Controllers\MyCtrl';
        $this->assertSame($class, $d->getClassName());
    }

    /**
     * @expectedExceptionMessage The provided callback class
     * @expectedException \Webiny\Component\Bootstrap\BootstrapException
     */
    public function testGetClassNameException()
    {
        $d = Dispatcher::mvcDispatcher('MyModule', 'MyCtrl', 'MyAct', ['test1', 'test2']);
        $d->setClassName('DoesntExist');
    }

    public function testIssueCallback()
    {
        $d = Dispatcher::mvcDispatcher('MyModule', 'MyCtrl', 'MyAct', ['test1', 'test2']);
        $d->issueCallback();
    }

}