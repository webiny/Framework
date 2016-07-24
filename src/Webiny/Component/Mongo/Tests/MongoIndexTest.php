<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Mongo\Tests;


use MongoDB\Model\BSONDocument;
use PHPUnit_Framework_TestCase;
use Webiny\Component\Mongo\Index\CompoundIndex;
use Webiny\Component\Mongo\Index\SingleIndex;
use Webiny\Component\Mongo\Index\SphereIndex;
use Webiny\Component\Mongo\Index\TextIndex;
use Webiny\Component\Mongo\Mongo;
use Webiny\Component\Mongo\MongoTrait;

class MongoIndexTest extends PHPUnit_Framework_TestCase
{
    use MongoTrait;

    const CONFIG = '/ExampleConfig.yaml';

    public static function setUpBeforeClass()
    {
        self::deleteAllTestCollections();
    }

    public static function tearDownAfterClass()
    {
        self::deleteAllTestCollections();
    }

    /**
     * @dataProvider driverSet
     */
    public function testIndexes(Mongo $mongo)
    {
        $collection = 'IndexCollection';
        $mongo->dropCollection($collection);

        $index = new SingleIndex('Name', 'name', false, true);
        $indexName = $mongo->createIndex($collection, $index);
        $this->assertEquals('Name', $indexName);

        $index = new CompoundIndex('TitleCategory', ['title', 'category']);
        $indexName = $mongo->createIndex($collection, $index);
        $this->assertEquals('TitleCategory', $indexName);

        $index = new TextIndex('CompoundTextIndex', ['title', 'category']);
        $indexName = $mongo->createIndex($collection, $index);
        $this->assertEquals('CompoundTextIndex', $indexName);

        // Drop index because we can't have 2 text indexes in one collection
        $mongo->dropIndex($collection, 'CompoundTextIndex');

        $index = new TextIndex('SingleTextIndex', 'name');
        $indexName = $mongo->createIndex($collection, $index);
        $this->assertEquals('SingleTextIndex', $indexName);

        $index = new SphereIndex('Location', 'location');
        $indexName = $mongo->createIndex($collection, $index);
        $this->assertEquals('Location', $indexName);

        $indexes = $mongo->listIndexes($collection);
        $this->assertEquals(5, count($indexes));
    }

    /**
     * @dataProvider driverSet
     */
    public function testDropIndexes(Mongo $mongo)
    {
        $collection = 'IndexCollection';
        $mongo->dropIndex($collection, 'Name');

        $indexes = $mongo->listIndexes($collection);
        $this->assertNotContains('Name', $indexes);
        $this->assertEquals(4, count($indexes));

        $mongo->dropIndexes($collection);
        // _id_ index is always present so the count is 1 at least
        $this->assertCount(1, $mongo->listIndexes($collection));
    }

    public function driverSet()
    {
        Mongo::setConfig(realpath(__DIR__ . '/' . self::CONFIG));

        return [
            [$this->mongo()]
        ];
    }

    private static function deleteAllTestCollections()
    {
        self::mongo()->dropCollection('IndexCollection');
    }
}