<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\TemplateEngine\Tests;

use Webiny\Component\TemplateEngine\TemplateEngine;

class TemplateEngineClassTest extends \PHPUnit_Framework_TestCase
{
    public function testSetConfig()
    {
        TemplateEngine::setConfig(__DIR__ . '/ExampleConfig.yaml');
    }

    public function testGetConfig()
    {
        $config = TemplateEngine::getConfig();

        $this->assertSame('/var/tmp/smarty/cache', $config->get('Engines.Smarty.CacheDir'));
    }
}