<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Config\Tests;


use Webiny\Component\Config\Config;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    function testYamlConfig()
    {
        $yamlConfig = realpath(__DIR__ . '/Configs/config.yaml');
        $config = Config::getInstance()->yaml($yamlConfig);
        $this->assertInstanceOf('\Webiny\Component\Config\ConfigObject', $config);
        $this->assertEquals('Royal Oak', $config->get('bill-to.address.city'));
    }

    function testJsonConfig()
    {
        $jsonConfig = realpath(__DIR__ . '/Configs/config.json');
        $config = Config::getInstance()->json($jsonConfig);
        $this->assertInstanceOf('\Webiny\Component\Config\ConfigObject', $config);
        $this->assertEquals('Webiny', $config->get('website.name'));
    }

    function testMissingFile()
    {
        $this->setExpectedException('\Webiny\Component\Config\ConfigException');
        $jsonConfig = realpath(__DIR__ . '/Configs/configMissing.json');
        Config::getInstance()->json($jsonConfig);
    }

    function testPhpConfig()
    {
        $phpConfig = realpath(__DIR__ . '/Configs/config.php');
        $config = Config::getInstance()->php($phpConfig);
        $this->assertInstanceOf('\Webiny\Component\Config\ConfigObject', $config);
        $this->assertEquals('www.webiny.com', $config->get('default.url'));
    }

    function testIniConfig()
    {
        $iniConfig = realpath(__DIR__ . '/Configs/config.ini');
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

    function testSaveAsYaml()
    {
        $phpConfig = realpath(__DIR__ . '/Configs/config.php');
        $yamlConfig = __DIR__ . '/Configs/savedConfig.yaml';
        $config = Config::getInstance()->php($phpConfig);
        $config->saveAsYaml($yamlConfig);
        $this->assertFileExists($yamlConfig);
        $config = Config::getInstance()->yaml($yamlConfig);
        $this->assertEquals('www.webiny.com', $config->get('default.url'));
        unlink($yamlConfig);
    }

    function testSaveAsIni()
    {
        $phpConfig = realpath(__DIR__ . '/Configs/config.php');
        $iniConfig = __DIR__ . '/Configs/savedConfig.ini';
        $config = Config::getInstance()->php($phpConfig);
        $config->saveAsIni($iniConfig);
        $this->assertFileExists($iniConfig);
        $config = Config::getInstance()->ini($iniConfig);
        $this->assertEquals('www.webiny.com', $config->get('default.url'));
        unlink($iniConfig);
    }

    function testSaveAsJson()
    {
        $phpConfig = realpath(__DIR__ . '/Configs/config.php');
        $jsonConfig = __DIR__ . '/Configs/savedConfig.json';
        $config = Config::getInstance()->php($phpConfig);
        $config->saveAsJson($jsonConfig);
        $this->assertFileExists($jsonConfig);
        $config = Config::getInstance()->json($jsonConfig);
        $this->assertEquals('www.webiny.com', $config->get('default.url'));
        unlink($jsonConfig);
    }

    function testSaveAsPhp()
    {
        $yamlConfig = realpath(__DIR__ . '/Configs/config.yaml');
        $phpConfig = __DIR__ . '/Configs/savedConfig.php';
        $config = Config::getInstance()->yaml($yamlConfig);
        $config->saveAsPhp($phpConfig);
        $this->assertFileExists($phpConfig);
        $config = Config::getInstance()->php($phpConfig);
        $this->assertEquals('Royal Oak', $config->get('bill-to.address.city'));
        unlink($phpConfig);
    }
}