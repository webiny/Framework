<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\TemplateEngine\Tests\Bridge;

use Webiny\Component\Config\ConfigObject;
use Webiny\Component\TemplateEngine\Bridge\TemplateEngine;

class TemplateEngineTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        \Webiny\Component\TemplateEngine\TemplateEngine::setConfig(__DIR__ . '/../ExampleConfig.yaml');
    }

    public function testGetInstance()
    {
        $config = new ConfigObject([
                                       'CompileDir'  => __DIR__ . '/Templates/Compile',
                                       'CacheDir'    => __DIR__ . '/Templates/Cache',
                                       'TemplateDir' => __DIR__ . '/Templates'
                                   ]
        );

        $this->assertInstanceOf('\Webiny\Component\TemplateEngine\Bridge\TemplateEngineInterface',
                                TemplateEngine::getInstance('Smarty', $config)
        );
    }

    /**
     * @expectedException \Webiny\Component\TemplateEngine\Bridge\TemplateEngineException
     */
    public function testGetInstanceException()
    {
        TemplateEngine::getInstance('Fake', new ConfigObject([]));
    }
}