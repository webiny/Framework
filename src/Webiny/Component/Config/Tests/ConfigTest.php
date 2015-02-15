<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Config\Tests;


use Webiny\Component\Config\Config;
use Webiny\Component\Config\ConfigException;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    function testYamlConfig()
    {
        $yamlConfig = __DIR__ . '/Configs/config.yaml';
        $config = Config::getInstance()->yaml($yamlConfig);
        $this->assertInstanceOf('\Webiny\Component\Config\ConfigObject', $config);
        $this->assertEquals('Royal Oak', $config->get('bill-to.address.city'));
    }

    function testJsonConfig()
    {
        $jsonConfig = __DIR__ . '/Configs/config.json';
        $config = Config::getInstance()->json($jsonConfig);
        $this->assertInstanceOf('\Webiny\Component\Config\ConfigObject', $config);
        $this->assertEquals('Webiny', $config->get('website.name'));
    }

    /**
     * @expectedException \Webiny\Component\Config\ConfigException
     */
    function testMissingFile()
    {
        $jsonConfig = __DIR__ . '/Configs/configMissing.json';
        Config::getInstance()->json($jsonConfig);
    }

    /**
     * @expectedException \Webiny\Component\Config\ConfigException
     */
    function testInvalidFilePath()
    {
        $jsonConfig = __DIR__ . '/Configs';
        Config::getInstance()->json($jsonConfig);
    }

    function testPhpConfig()
    {
        $phpConfig = __DIR__ . '/Configs/config.php';
        $config = Config::getInstance()->php($phpConfig);
        $this->assertInstanceOf('\Webiny\Component\Config\ConfigObject', $config);
        $this->assertEquals('www.webiny.com', $config->get('default.url'));
    }

    function testIniConfig()
    {
        $iniConfig = __DIR__ . '/Configs/config.ini';
        $config = Config::getInstance()->ini($iniConfig);
        $this->assertInstanceOf('\Webiny\Component\Config\ConfigObject', $config);
        $this->assertEquals('coolProperty', $config->group2->newProperty);
    }

    function testParseResource()
    {
        $resource = ['application' => 'development'];
        $config = Config::getInstance()->parseResource($resource);
        $this->assertInstanceOf('\Webiny\Component\Config\ConfigObject', $config);
        $this->assertEquals('development', $config->application);
    }
}