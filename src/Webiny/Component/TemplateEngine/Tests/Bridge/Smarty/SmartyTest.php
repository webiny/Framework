<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\TemplateEngine\Tests\Bridge\Smarty;

use Webiny\Component\Config\ConfigObject;
use Webiny\Component\TemplateEngine\Bridge\Smarty\Smarty;
use Webiny\Component\TemplateEngine\TemplateEngine;
use Webiny\Component\TemplateEngine\Tests\Bridge\Smarty\Mocks\DemoComponent;
use Webiny\Component\TemplateEngine\Tests\Bridge\Smarty\Mocks\DemoComponentPlugin;
use Webiny\Component\TemplateEngine\Tests\Bridge\Smarty\Mocks\PluginMock;

/**
 * Class SmartyTest
 * @package Webiny\Component\TemplateEngine\Tests\Bridge\Smarty
 * @runTestsInSeparateProcesses
 */
class SmartyTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        TemplateEngine::setConfig(__DIR__ . '/../../ExampleConfig.yaml');
    }

    /**
     * @expectedException \Webiny\Component\TemplateEngine\Bridge\Smarty\SmartyException
     * @expectedExceptionMessage Configuration error, "CompileDir" is missing.
     */
    public function testConstructNoCompileDirException()
    {
        $config = new ConfigObject([]);
        $smarty = new Smarty($config);
    }

    /**
     * @expectedException \Webiny\Component\TemplateEngine\Bridge\Smarty\SmartyException
     * @expectedExceptionMessage Configuration error, "CacheDir" is missing.
     */
    public function testConstructNoCacheDirException()
    {
        $config = new ConfigObject([
                                       'CompileDir' => '/tmp/'
                                   ]
        );

        $smarty = new Smarty($config);
    }

    /**
     * @expectedException \Webiny\Component\TemplateEngine\Bridge\Smarty\SmartyException
     * @expectedExceptionMessage Configuration error, "TemplateDir" is missing.
     */
    public function testConstructNoTemplateDirException()
    {
        $config = new ConfigObject([
                                       'CompileDir' => '/tmp/',
                                       'CacheDir'   => '/tmp/cache',
                                   ]
        );

        $smarty = new Smarty($config);
    }

    public function testConstruct()
    {
        $config = new ConfigObject([
                                       'CompileDir'  => '/tmp/',
                                       'CacheDir'    => '/tmp/',
                                       'TemplateDir' => __DIR__
                                   ]
        );

        $smarty = new Smarty($config);

        $this->assertInstanceOf('\Webiny\Component\TemplateEngine\Bridge\Smarty\Smarty', $smarty);
    }

    /**
     * @dataProvider dataProvider
     */
    public function testSetGetCompileDir(Smarty $smarty)
    {
        $this->assertSame(__DIR__ . '/Templates/Compile', $smarty->getCompileDir());
        $smarty->setCompileDir('/tmp/new-compile');
        $this->assertSame('/tmp/new-compile', $smarty->getCompileDir());
    }

    /**
     * @dataProvider dataProvider
     */
    public function testSetGetCacheDir(Smarty $smarty)
    {
        $this->assertSame(__DIR__ . '/Templates/Cache', $smarty->getCacheDir());
        $smarty->setCacheDir('/tmp/new-cache');
        $this->assertSame('/tmp/new-cache', $smarty->getCacheDir());
    }

    /**
     * @dataProvider dataProvider
     */
    public function testGetTemplateDir(Smarty $smarty)
    {
        $this->assertSame(__DIR__ . '/Templates', $smarty->getTemplateDir());
    }

    /**
     * @dataProvider             dataProvider
     * @expectedException \Webiny\Component\TemplateEngine\Bridge\Smarty\SmartyException
     * @expectedExceptionMessage The template dir
     */
    public function testSetFakeTemplateDir(Smarty $smarty)
    {
        $smarty->setTemplateDir('/theme/templates');
    }

    /**
     * @dataProvider dataProvider
     */
    public function testSetGetForceCompile(Smarty $smarty)
    {
        $this->assertFalse($smarty->getForceCompile());
        $smarty->setForceCompile(true);
        $this->assertTrue($smarty->getForceCompile());
    }

    /**
     * @dataProvider dataProvider
     */
    public function testFetch(Smarty $smarty)
    {
        $result = $smarty->fetch('Test.tpl');
        $this->assertSame('Hello World', $result);
    }

    /**
     * @dataProvider dataProvider
     */
    public function testFetchWithParams(Smarty $smarty)
    {
        $result = $smarty->fetch('TestWithParams.tpl', [
                'name'      => 'Batman',
                'otherName' => 'Superman'
            ]
        );
        $this->assertSame('Hello Batman. My name is Superman.', $result);
    }

    /**
     * @dataProvider dataProvider
     */
    public function testAssign(Smarty $smarty)
    {
        $smarty->assign('name', 'Batman');
        $smarty->assign('otherName', 'Superman');

        $result = $smarty->fetch('TestWithParams.tpl');
        $this->assertSame('Hello Batman. My name is Superman.', $result);
    }

    /**
     * @dataProvider dataProvider
     */
    public function testRegisterPlugin(Smarty $smarty)
    {
        $plugin = new PluginMock();
        $smarty->registerPlugin($plugin);

        $smarty->assign('name', 'world');
        $result = $smarty->fetch('TestPlugin.tpl');
        $this->assertSame('Hello WORLD', $result);
    }

    /**
     * @dataProvider dataProvider
     */
    public function testRegisterExtensions(Smarty $smarty)
    {
        DemoComponent::setConfig(__DIR__ . '/Mocks/DemoComponentConfig.yaml');
        $smarty->registerExtensions();

        $smarty->assign('name', 'WORLD');
        $result = $smarty->fetch('DemoComponent.tpl');
        $this->assertSame('Hello world', $result);
    }

    public function dataProvider()
    {
        $config = new ConfigObject([
                                       'CompileDir'  => __DIR__ . '/Templates/Compile',
                                       'CacheDir'    => __DIR__ . '/Templates/Cache',
                                       'TemplateDir' => __DIR__ . '/Templates'
                                   ]
        );

        $smarty = new Smarty($config);

        return [[$smarty]];
    }

}