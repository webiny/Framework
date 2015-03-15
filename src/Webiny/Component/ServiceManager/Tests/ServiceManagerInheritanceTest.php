<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\ServiceManager\Tests;


use PHPUnit_Framework_TestCase;
use Webiny\Component\Config\ConfigObject;
use Webiny\Component\ServiceManager\ServiceManager;

class ServiceManagerInheritanceTest extends PHPUnit_Framework_TestCase
{

    protected static $services = [
        'Abstract' => [
            'Abstract' => true,
            'Class'    => '\Webiny\Component\ServiceManager\Tests\Classes\AbstractService'
        ],

        'Real'     => [
            'Parent'    => '@Inheritance.Abstract',
            'Arguments' => ['Webiny']
        ]

    ];

    public function testInheritance()
    {
        $servicesConfig = new ConfigObject(self::$services);
        ServiceManager::getInstance()->registerServices('Inheritance', $servicesConfig);
        $service = ServiceManager::getInstance()->getService('Inheritance.Real');
        $this->assertEquals('Webiny', $service->getValue());
    }
}