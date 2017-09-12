<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Mongo\Tests;


use MongoDB\Model\CollectionInfo;
use PHPUnit_Framework_TestCase;
use Webiny\Component\Mongo\Mongo;
use Webiny\Component\Mongo\MongoTrait;

class MongoTest extends PHPUnit_Framework_TestCase
{
    use MongoTrait;

    const CONFIG = '/ExampleConfig.yaml';

    /**
     * @dataProvider driverSet
     */
    public function testConstructor($mongo)
    {
        $this->assertInstanceOf(Mongo::class, $mongo);
    }

    /**
     * @dataProvider driverSet
     */
    public function testMongo(Mongo $mongo)
    {
        $collection = 'TestCollection';
        $mongo->dropCollection($collection);

        // Test create collection
        $mongo->createCollection($collection);
        $collections = $mongo->listCollections();
        /* @var $c CollectionInfo */
        $collectionNames = [];
        foreach ($collections as $c) {
            $collectionNames[] = $c->getName();
        }
        $this->assertContains('Mongo_' . $collection, $collectionNames);

        // Test insert data
        $data = ['name' => 'Webiny'];
        $result = $mongo->insertOne($collection, $data);
        $this->assertEquals(1, $result->getInsertedCount());
        $this->assertEquals(1, $mongo->count($collection));

        // Get new record ID
        $id = $result->getInsertedId();

        // Test update
        $res = $mongo->update($collection, ['_id' => $id], ['$set' => ['name' => 'Updated Webiny']]);
        $this->assertEquals(1, $res->getModifiedCount());

        // Test findOne
        $data = $mongo->findOne($collection, ['_id' => $id]);
        $this->assertEquals('Updated Webiny', $data['name']);

        // Test find
        $data = $mongo->find($collection, ['name' => 'Updated Webiny']);
        $this->assertCount(1, $data);

        // Test insertMany
        $mongo->insertMany($collection, [
            ['name' => 'Webiny', 'tag' => 1],
            ['name' => 'Webiny', 'tag' => 2],
            ['name' => 'Webiny', 'tag' => 3],
            ['name' => 'Webiny', 'tag' => 4],
            ['name' => 'Webiny', 'tag' => 5]
        ]);

        $this->assertEquals(6, $mongo->count($collection));

        // Test find with offset
        $results = $mongo->find($collection, ['name' => 'Webiny'], [], 1, 2);
        $this->assertEquals(3, $results[0]['tag']);
        $results = $mongo->find($collection, ['name' => 'Webiny'], ['tag' => -1], 2, 3);
        $this->assertCount(2, $results);
        $this->assertEquals(2, $results[0]['tag']);
        $this->assertEquals(1, $results[1]['tag']);

        // Test remove data
        $mongo->delete($collection, ['_id' => $id]);
        $this->assertEquals(5, $mongo->count($collection));

        // Test drop collection
        $mongo->dropCollection($collection);
        $collections = $mongo->listCollections();
        /* @var $c CollectionInfo */
        $collectionNames = [];
        foreach ($collections as $c) {
            $collectionNames[] = $c->getName();
        }
        $this->assertFalse(in_array($collection, $collectionNames));

        // Test isId()
        $id = '12345678absdfgrtuierwe12';
        $this->assertFalse($mongo->isId($id));

        $id = 'aaaabbbbcccc 11122223333';
        $this->assertFalse($mongo->isId($id));

        $id = '543c1d846803fa76058b458b';
        $this->assertTrue($mongo->isId($id));
    }

    public function driverSet()
    {
        Mongo::setConfig(realpath(__DIR__ . '/' . self::CONFIG));

        return [
            [$this->mongo()]
        ];
    }

}