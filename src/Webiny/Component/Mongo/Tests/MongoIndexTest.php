<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Mongo\Tests;


use PHPUnit_Framework_TestCase;
use Webiny\Component\Mongo\Index\CompoundIndex;
use Webiny\Component\Mongo\Index\SingleIndex;
use Webiny\Component\Mongo\Index\TextIndex;
use Webiny\Component\Mongo\Mongo;
use Webiny\Component\Mongo\MongoTrait;

class MongoIndexTest extends PHPUnit_Framework_TestCase
{
    use MongoTrait;

    const CONFIG = '/ExampleConfig.yaml';

    /**
     * @dataProvider driverSet
     */
    public function testSingleIndex(Mongo $mongo)
    {
        $collection = 'TestIndexCollection';
        $mongo->dropCollection($collection);

        $index = new SingleIndex('Name', 'name', false, true);
        $mongo->createIndex($collection, $index);

        $index = new CompoundIndex('TitleCategory', ['title', 'category']);
        $mongo->createIndex($collection, $index);

        $index = new TextIndex('Title', ['title', 'category']);
        $mongo->createIndex($collection, $index);

        $indexes = $mongo->getIndexInfo($collection);
        $this->assertEquals(4, count($indexes));
    }

    public function driverSet()
    {
        Mongo::setConfig(realpath(__DIR__ . '/' . self::CONFIG));

        return [
            [$this->mongo()]
        ];
    }

}