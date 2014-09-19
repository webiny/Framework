<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Http\Tests\Request;


use Webiny\Component\Http\Http;
use Webiny\Component\Http\Request\Env;

class EnvTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Http::setConfig(__DIR__ . '/../ExampleConfig.yaml');
    }

    public function testConstructor()
    {
        $env = new Env();
        $this->assertInstanceOf('\Webiny\Component\Http\Request\Env', $env);
    }

    public function testGet()
    {
        $_ENV = ["APP_ENV" => "development"];
        $env = new Env();

        $this->assertSame("development", $env->get("APP_ENV"));
        $this->assertNull($env->get("NON_EXISTING"));
        $this->assertSame("production", $env->get("NON_EXISTING_2", "production"));
    }

    public function testGetAll()
    {
        $_ENV = [
            "APP_ENV"  => "development",
            "DEBUG_IP" => "127.0.0.1"
        ];

        $env = new Env();
        $this->assertSame($_ENV, $env->getAll());
    }
}