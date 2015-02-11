<?php

namespace Webiny\Component\Bootstrap\Tests;

use Webiny\Component\Bootstrap\Environment;

class EnvironmentTest extends \PHPUnit_Framework_TestCase
{

    public function testInitializeEnvironment()
    {
        $env = Environment::getInstance();
        $env->initializeEnvironment(__DIR__ . '/DemoApp/');

        // validate the environment
        $this->assertSame('Production', $env->getCurrentEnvironmentName());
        $this->assertSame('Webiny\Component\Bootstrap\Tests\DemoApp', $env->getApplicationConfig()->get('Namespace'));
        $this->assertSame('Value', $env->getCurrentEnvironmentConfig()->get('SomeVar'));
    }

    /**
     * @expectedException \Webiny\Component\Bootstrap\BootstrapException
     * @expectedExceptionMessage Unable to read app config file
     */
    public function testInitializeEnvironmentException()
    {
        Environment::getInstance()->initializeEnvironment('');
    }

    public function testGetApplicationConfig()
    {
        $env = Environment::getInstance();
        $env->initializeEnvironment(__DIR__ . '/DemoApp/');

        $appConfig = $env->getApplicationConfig()->toArray();

        $appConfigArr = [
            'Namespace'    => 'Webiny\Component\Bootstrap\Tests\DemoApp',
            'Environments' => [
                'Production' => [
                    'SomeVar'   => 'Value'
                ]
            ]
        ];

        $this->assertSame($appConfigArr, $appConfig);
    }

    public function testGetApplicationAbsolutePath()
    {
        $env = Environment::getInstance();
        $env->initializeEnvironment(__DIR__ . '/DemoApp/');

        $path = $env->getApplicationAbsolutePath();

        $this->assertSame(__DIR__.DIRECTORY_SEPARATOR.'DemoApp'.DIRECTORY_SEPARATOR, $path);
    }

    public function testGetComponentConfigs()
    {
        $env = Environment::getInstance();
        $env->initializeEnvironment(__DIR__ . '/DemoApp/');

        $componentConfigs = $env->getComponentConfigs()->toArray();

        $this->assertArrayHasKey('Router', $componentConfigs);
    }

    public function getCurrentEnvironmentName()
    {
        $env = Environment::getInstance();
        $env->initializeEnvironment(__DIR__ . '/DemoApp/');

        $this->assertSame('Production', $env->getCurrentEnvironmentName());
    }

    public function testGetCurrentEnvironmentConfig()
    {
        $env = Environment::getInstance();
        $env->initializeEnvironment(__DIR__ . '/DemoApp/');

        $envConfig = $env->getCurrentEnvironmentConfig()->toArray();
        $envConfigArr = [
                    'SomeVar'   => 'Value'
        ];

        $this->assertSame($envConfigArr, $envConfig);
    }

    
}