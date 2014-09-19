<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Logger\Tests;


use PHPUnit_Framework_TestCase;
use Webiny\Component\Logger\Logger;
use Webiny\Component\Logger\LoggerTrait;

class LoggerTest extends PHPUnit_Framework_TestCase
{
    use LoggerTrait;

    const CONFIG = '/ExampleConfig.yaml';

    /**
     * @dataProvider DriverSet
     */
    function testConstructor($logger)
    {
        $this->assertInstanceOf('\Webiny\Component\Logger\Logger', $logger);
    }

    /**
     * @dataProvider DriverSet
     */
    function testLogger(Logger $logger)
    {
        $fileLocation = Logger::getConfig()->Parameters['Logger.LogFile'];
        $logger->error('Test error message!', ['customValue' => 'Webiny']);
        $this->assertFileExists($fileLocation);
        $logContents = file_get_contents($fileLocation);
        // Make sure we have our log message in the file
        $this->assertTrue(strpos($logContents, 'Test error message!') !== false);
        // Make sure context is properly written to log
        $this->assertTrue(strpos($logContents, 'customValue') !== false);
        // Make sure FileLineProcessor was triggered
        $this->assertTrue(strpos($logContents, 'file') !== false);
        $this->assertTrue(strpos($logContents, 'line') !== false);
        // Make sure MemoryUsageProcessor was triggered
        $this->assertTrue(strpos($logContents, 'memoryUsage') !== false);
        unlink($fileLocation);
    }

    function DriverSet()
    {
        Logger::setConfig(realpath(__DIR__ . '/' . self::CONFIG));

        return [
            [$this->logger('Webiny')]
        ];
    }

}