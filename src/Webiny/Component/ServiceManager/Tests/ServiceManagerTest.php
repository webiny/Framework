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
use Webiny\Component\ServiceManager\Tests\Classes\MainService;

class ServiceManagerTest extends PHPUnit_Framework_TestCase
{

    protected static $_services = [
        'Parameters'    => [
            'MainService.Class' => '\Webiny\Component\ServiceManager\Tests\Classes\MainService'
        ],
        'MainService'   => [
            'Class'     => '%MainService.Class%',
            'Arguments' => [
                'first'  => 'FirstArgument',
                'second' => '@SecondService',
                'third'  => [
                    'Object'          => '\Webiny\Component\ServiceManager\Tests\Classes\ConstructorArgumentClass',
                    'ObjectArguments' => ['SomeParameter']
                ]
            ],
            'Calls'     => [
                [
                    'setCallValue',
                    ['Webiny']
                ]
            ]
        ],

        'SecondService' => [
            'Factory'         => '\Webiny\Component\ServiceManager\Tests\Classes\SecondService',
            'Method'          => 'getObject',
            'MethodArguments' => ['InjectedServiceValue']
        ]
    ];

    public function testServiceManager()
    {
        $mainServiceConfig = new ConfigObject(self::$_services['MainService']);
        $secondServiceConfig = new ConfigObject(self::$_services['SecondService']);
        ServiceManager::getInstance()->registerParameters(self::$_services['Parameters']);
        ServiceManager::getInstance()->registerService('MainService', $mainServiceConfig);
        ServiceManager::getInstance()->registerService('SecondService', $secondServiceConfig);

        /* @var $mainService MainService */
        $mainService = ServiceManager::getInstance()->getService('MainService');

        $this->assertEquals('InjectedServiceValue', $mainService->getInjectedServiceValue());
        $this->assertEquals('Webiny', $mainService->getCallValue());
        $this->assertEquals('FirstArgument', $mainService->getFirstArgumentValue());
        $checkInstance = '\Webiny\Component\ServiceManager\Tests\Classes\ConstructorArgumentClass';
        $this->assertInstanceOf($checkInstance, $mainService->getSomeInstance());
        $this->assertEquals('SomeParameter', $mainService->getSomeInstance()->getConstructorParameterValue());
    }
}