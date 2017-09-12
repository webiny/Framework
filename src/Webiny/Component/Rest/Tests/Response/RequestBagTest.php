<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Rest\Tests\Response;

use Webiny\Component\Rest\Response\RequestBag;

class RequestBagTest extends \PHPUnit_Framework_TestCase
{

    public function testConstructor()
    {
        $instance = new RequestBag();
        $this->assertInstanceOf(RequestBag::class, $instance);
    }

    public function testSetGetApi()
    {
        $rb = new RequestBag();
        $rb->setApi('ExampleApi');

        $this->assertSame('ExampleApi', $rb->getApi());
    }

    public function testSetGetClassData()
    {
        $rb = new RequestBag();
        $rb->setClassData(['data' => 'foo']);

        $this->assertSame(['data' => 'foo'], $rb->getClassData());
    }

    public function testSetGetMethodData()
    {
        $rb = new RequestBag();
        $rb->setMethodData(['data' => 'foo']);

        $this->assertSame(['data' => 'foo'], $rb->getMethodData());
    }

    public function testSetGetMethodParameters()
    {
        $rb = new RequestBag();
        $rb->setMethodParameters(['data' => 'foo']);

        $this->assertSame(['data' => 'foo'], $rb->getMethodParameters());
    }

    public function testSetClassInstance()
    {
        $rb = new RequestBag();
        $rb->setClassInstance('instance');

        $this->assertSame('instance', $rb->getClassInstance());
    }
}