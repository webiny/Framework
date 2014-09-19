<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\TemplateEngine\Tests;

use Webiny\Component\TemplateEngine\TemplateEngine;
use Webiny\Component\TemplateEngine\TemplateEngineLoader;

class TemplateEngineLoaderTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        TemplateEngine::setConfig(__DIR__ . '/ExampleConfig.yaml');
    }

    public function testGetInstance()
    {
        $this->assertInstanceOf('\Webiny\Component\TemplateEngine\Bridge\TemplateEngineInterface',
                                TemplateEngineLoader::getInstance('Smarty')
        );
    }

    /**
     * @expectedException \Webiny\Component\TemplateEngine\TemplateEngineException
     * @expectedExceptionMessage Unable to read driver configuration
     */
    public function testGetInstanceException()
    {
        TemplateEngineLoader::getInstance('Fake');
    }
}