<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\TemplateEngine\Tests;

use Webiny\Component\TemplateEngine\TemplateEngine;
use Webiny\Component\TemplateEngine\TemplateEngineTrait;

class TemplateEngineTraitTest extends \PHPUnit_Framework_TestCase
{
    use TemplateEngineTrait;

    public function setUp()
    {
        TemplateEngine::setConfig(__DIR__ . '/ExampleConfig.yaml');
    }

    public function testGetInstance()
    {
        $this->assertInstanceOf('\Webiny\Component\TemplateEngine\Bridge\TemplateEngineInterface',
                                $this->templateEngine('Smarty')
        );
    }

    /**
     * @expectedException \Webiny\Component\TemplateEngine\TemplateEngineException
     * @expectedExceptionMessage Unable to read driver configuration
     */
    public function testGetInstanceException()
    {
        $this->templateEngine('Fake');
    }
}