<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Mongo\Tests;


use PHPUnit_Framework_TestCase;
use Webiny\Component\Mongo\Index\SingleIndex;
use Webiny\Component\Mongo\Mongo;
use Webiny\Component\Mongo\MongoTrait;

class MongoIndexTest extends PHPUnit_Framework_TestCase
{
    use MongoTrait;

    const CONFIG = '/ExampleConfig.yaml';

    /**
     * @dataProvider driverSet
     */
    function testSingleIndex(Mongo $mongo)
    {
        $collection = 'TestIndexCollection';
        $mongo->dropCollection($collection);

        $index = new SingleIndex('Name', 'name', false, true);
        $res = $mongo->createIndex($collection, $index);

        $this->assertEquals(1, $res['numIndexesAfter'] - $res['numIndexesBefore']);
    }

    function driverSet()
    {
        Mongo::setConfig(realpath(__DIR__ . '/' . self::CONFIG));

        return [
            [$this->mongo()]
        ];
    }

}