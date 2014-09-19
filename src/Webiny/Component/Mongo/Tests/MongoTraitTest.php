<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Mongo\Tests;

use Webiny\Component\Mongo\Mongo;
use Webiny\Component\Mongo\MongoTrait;

class MongoTraitTest extends \PHPUnit_Framework_TestCase
{
    use MongoTrait;

    const CONFIG = 'ExampleConfig.yaml';

    function setUp()
    {
        Mongo::setConfig(realpath(__DIR__ . '/' . self::CONFIG));
    }

    function testMongo()
    {
        $this->assertInstanceOf('\Webiny\Component\Mongo\Mongo', $this->mongo());
    }
}