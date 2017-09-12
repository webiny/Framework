<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Rest\Tests;

use Webiny\Component\Rest\Rest;

/**
 * Class RestTest
 * @package Webiny\Component\Rest\Tests
 */
class RestTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        Rest::setConfig(__DIR__ . '/Mocks/MockRestConfig.yaml');
    }

    /**
     * @throws \Webiny\Component\Rest\RestException
     */
    public function testInitRest()
    {
        $rest = Rest::initRest('ExampleApi', 'http://www.example.com/services/tests/mocks/mock-api-class/test');

        $this->assertInstanceOf(Rest::class, $rest);
    }

    public function testSetGetEnv()
    {
        $rest = Rest::initRest('ExampleApi', 'http://www.example.com/services/tests/mocks/mock-api-class/test');

        $this->assertSame(Rest::ENV_PRODUCTION, $rest->getEnvironment());
        $rest->setEnvironment(Rest::ENV_DEVELOPMENT);
        $this->assertSame(Rest::ENV_DEVELOPMENT, $rest->getEnvironment());
    }
}