<?php

namespace Webiny\Component\Bootstrap\Tests\ApplicationClasses;

use Webiny\Component\Bootstrap\Environment;
use Webiny\Component\Bootstrap\ApplicationClasses\Application;

class RouterTest extends \PHPUnit_Framework_TestCase
{
    function setUp()
    {
        $_SERVER = [
            'REQUEST_URI' => '/',
            'SCRIPT_NAME' => 'index.php',
            'SERVER_NAME' => 'www.myapp.com',
        ];
    }

    public function testConstructor()
    {
        $env = Environment::getInstance();
        $env->initializeEnvironment(__DIR__ . '/../DemoApp/');

        new Application($env);
    }

    public function testGetNamespace()
    {
        $env = Environment::getInstance();
        $env->initializeEnvironment(__DIR__ . '/../DemoApp/');

        $app = new Application($env);

        $this->assertSame('Webiny\Component\Bootstrap\Tests\DemoApp', $app->getNamespace());
    }

    public function testGetAbsolutePath()
    {
        $env = Environment::getInstance();
        $env->initializeEnvironment(__DIR__ . '/../DemoApp/');

        $app = new Application($env);

        $this->assertSame(__DIR__ . '/../DemoApp/', $app->getAbsolutePath());
    }

    public function testGetWebPath()
    {
        $env = Environment::getInstance();
        $env->initializeEnvironment(__DIR__ . '/../DemoApp/');

        $app = new Application($env);

        $this->assertSame('http://www.myapp.com/', $app->getWebPath());
    }

    public function testGetEnvironmentName()
    {
        $env = Environment::getInstance();
        $env->initializeEnvironment(__DIR__ . '/../DemoApp/');

        $app = new Application($env);

        $this->assertSame('Production', $app->getEnvironmentName());
    }

    public function testIsProductionEnvironment()
    {
        $env = Environment::getInstance();
        $env->initializeEnvironment(__DIR__ . '/../DemoApp/');

        $app = new Application($env);

        $this->assertTrue($app->isProductionEnvironment());
    }

    public function testShowErrors()
    {
        $env = Environment::getInstance();
        $env->initializeEnvironment(__DIR__ . '/../DemoApp/');

        $app = new Application($env);

        $this->assertFalse($app->showErrors());
    }

    public function testGetApplicationConfig()
    {
        $env = Environment::getInstance();
        $env->initializeEnvironment(__DIR__ . '/../DemoApp/');

        $app = new Application($env);

        $appConfig = $app->getApplicationConfig()->toArray();

        $appConfigArr = [
            'Namespace'    => 'Webiny\Component\Bootstrap\Tests\DemoApp',
            'Environments' => [
                'Production' => [
                    'SomeVar' => 'Value'
                ]
            ]
        ];

        $this->assertSame($appConfigArr, $appConfig);
    }

    public function testGetApplicationConfigQuery()
    {
        $env = Environment::getInstance();
        $env->initializeEnvironment(__DIR__ . '/../DemoApp/');

        $app = new Application($env);

        $appConfig = $app->getApplicationConfig('Environments.Production.SomeVar');

        $this->assertSame('Value', $appConfig);
    }

    public function testGetApplicationConfigQueryFalse()
    {
        $env = Environment::getInstance();
        $env->initializeEnvironment(__DIR__ . '/../DemoApp/');

        $app = new Application($env);

        $appConfig = $app->getApplicationConfig('Test');

        $this->assertNull($appConfig);
    }

    public function testGetApplicationConfigQueryDefault()
    {
        $env = Environment::getInstance();
        $env->initializeEnvironment(__DIR__ . '/../DemoApp/');

        $app = new Application($env);

        $appConfig = $app->getApplicationConfig('Test', 'default');

        $this->assertSame('default', $appConfig);
    }

    public function testGetCurrentEnvironmentConfig()
    {
        $env = Environment::getInstance();
        $env->initializeEnvironment(__DIR__ . '/../DemoApp/');

        $app = new Application($env);

        $envConfig = $app->getEnvironmentConfig()->toArray();
        $envConfigArr = [
            'SomeVar' => 'Value'
        ];

        $this->assertSame($envConfigArr, $envConfig);
    }

    public function testGetCurrentEnvironmentConfigQuery()
    {
        $env = Environment::getInstance();
        $env->initializeEnvironment(__DIR__ . '/../DemoApp/');

        $app = new Application($env);

        $envConfig = $app->getEnvironmentConfig('SomeVar');

        $this->assertSame('Value', $envConfig);
    }

    public function testGetCurrentEnvironmentConfigQueryFalse()
    {
        $env = Environment::getInstance();
        $env->initializeEnvironment(__DIR__ . '/../DemoApp/');

        $app = new Application($env);

        $envConfig = $app->getEnvironmentConfig('SomeVarTwo');

        $this->assertNull($envConfig);
    }

    public function testGetCurrentEnvironmentConfigQueryDefault()
    {
        $env = Environment::getInstance();
        $env->initializeEnvironment(__DIR__ . '/../DemoApp/');

        $app = new Application($env);

        $envConfig = $app->getEnvironmentConfig('SomeVarTwo', 'default');

        $this->assertSame('default', $envConfig);
    }

    public function testGetComponentConfig()
    {
        $env = Environment::getInstance();
        $env->initializeEnvironment(__DIR__ . '/../DemoApp/');

        $app = new Application($env);

        $componentConfigs = $app->getComponentConfig('Router')->toArray();

        $this->assertArrayHasKey('Routes', $componentConfigs);
    }

    public function testGetComponentConfigNull()
    {
        $env = Environment::getInstance();
        $env->initializeEnvironment(__DIR__ . '/../DemoApp/');

        $app = new Application($env);

        $result = $app->getComponentConfig('SomethingThatDoesntExist');

        $this->assertNull($result);
    }

    public function testGetComponentConfigQuery()
    {
        $env = Environment::getInstance();
        $env->initializeEnvironment(__DIR__ . '/../DemoApp/');

        $app = new Application($env);

        $result = $app->getComponentConfig('Router', 'Routes.StartPage.Path');

        $this->assertSame('/', $result);
    }

    public function testGetComponentConfigQueryFalse()
    {
        $env = Environment::getInstance();
        $env->initializeEnvironment(__DIR__ . '/../DemoApp/');

        $app = new Application($env);

        $result = $app->getComponentConfig('Router', 'Testing');

        $this->assertNull($result);
    }

    public function testGetComponentConfigQueryDefault()
    {
        $env = Environment::getInstance();
        $env->initializeEnvironment(__DIR__ . '/../DemoApp/');

        $app = new Application($env);

        $result = $app->getComponentConfig('Router', 'Testing', 'default');

        $this->assertSame('default', $result);
    }

    public function testView()
    {
        $env = Environment::getInstance();
        $env->initializeEnvironment(__DIR__ . '/../DemoApp/');

        $app = new Application($env);

        $this->assertInstanceOf('\Webiny\Component\Bootstrap\ApplicationClasses\View', $app->view());
    }

    public function testHtmlResponse()
    {
        $env = Environment::getInstance();
        $env->initializeEnvironment(__DIR__ . '/../DemoApp/');

        $app = new Application($env);
        $this->assertFalse($app->httpResponse());

        $app->view()->setTemplate($app->getAbsolutePath().'App/Modules/MyModule/Views/MyCtrl/MyAct.tpl');
        $this->assertInstanceOf('\Webiny\Component\Http\Response', $app->httpResponse());
    }
}