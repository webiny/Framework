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
use Webiny\Component\ServiceManager\Tests\Classes\ConstructorArgumentClass;
use Webiny\Component\ServiceManager\Tests\Classes\FactoryService;
use Webiny\Component\ServiceManager\Tests\Classes\InstanceService;
use Webiny\Component\ServiceManager\Tests\Classes\MainService;

class ServiceManagerTest extends PHPUnit_Framework_TestCase
{

    protected static $services = [
        'Parameters'      => [
            'MainService.Class' => MainService::class
        ],
        'MainService'     => [
            'Class'     => '%MainService.Class%',
            'Arguments' => [
                'first'    => 'FirstArgument',
                'factory'  => '@FactoryService',
                'instance' => '@InstanceService',
                'fourth'   => [
                    'Object'          => ConstructorArgumentClass::class,
                    'ObjectArguments' => ['SomeParameter', '@InstanceService']
                ]
            ],
            'Calls'     => [
                [
                    'setCallValue',
                    ['Webiny']
                ]
            ]
        ],
        'FactoryService'  => [
            'Factory'         => FactoryService::class,
            'Method'          => 'getObject',
            'MethodArguments' => ['InjectedServiceValue']
        ],
        'InstanceService' => [
            'Class' => InstanceService::class
        ]
    ];

    public function testServiceManager()
    {
        $mainServiceConfig = new ConfigObject(self::$services['MainService']);
        $secondServiceConfig = new ConfigObject(self::$services['FactoryService']);
        $thirdServiceConfig = new ConfigObject(self::$services['InstanceService']);
        ServiceManager::getInstance()->registerParameters(self::$services['Parameters']);
        ServiceManager::getInstance()->registerService('MainService', $mainServiceConfig);
        ServiceManager::getInstance()->registerService('FactoryService', $secondServiceConfig);
        ServiceManager::getInstance()->registerService('InstanceService', $thirdServiceConfig);

        /* @var $mainService MainService */
        $mainService = ServiceManager::getInstance()->getService('MainService');

        $this->assertEquals('InjectedServiceValue', $mainService->getFactoryService());
        $this->assertEquals('Webiny', $mainService->getCallValue());
        $this->assertEquals('FirstArgument', $mainService->getFirstArgumentValue());
        $this->assertInstanceOf(ConstructorArgumentClass::class, $mainService->getSomeInstance());
        $this->assertInstanceOf(InstanceService::class, $mainService->getInstanceService());
        $this->assertEquals('SomeParameter', $mainService->getSomeInstance()->getConstructorParameterValue());
    }
}