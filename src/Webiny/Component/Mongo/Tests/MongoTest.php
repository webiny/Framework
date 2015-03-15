<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Mongo\Tests;


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
        $this->assertInstanceOf('Webiny\Component\Mongo\Mongo', $mongo);
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
        $collectionNames = $mongo->getCollectionNames();
        $this->assertContains($collection, $collectionNames);

        // Test insert data
        $data = ['name' => 'Webiny'];
        $mongo->insert($collection, $data);
        $this->assertEquals(1, $mongo->count($collection));
        $this->assertArrayHasKey('_id', $data);

        // Get new record ID
        $id = $data['_id'];

        // Test update and findOne
        $mongo->update($collection, ['_id' => $id], ['name' => 'Updated Webiny']);
        $data = $mongo->findOne($collection, ['_id' => $id]);
        $this->assertEquals('Updated Webiny', $data['name']);

        // Test find
        $data = $mongo->find($collection, ['name' => 'Updated Webiny']);
        $this->assertCount(1, $data);

        // Test ensureIndex
        $res = $mongo->ensureIndex($collection, 'name');
        $this->assertEquals(1, $res['ok']);

        // Test remove data
        $mongo->remove($collection, ['_id' => $id]);
        $this->assertEquals(0, $mongo->count($collection));

        // Test save
        $data = ['name' => 'Webiny Save'];
        $res = $mongo->save($collection, $data);
        $this->assertEquals(1, $res['ok']);

        // Test drop collection
        $mongo->dropCollection($collection);
        $this->assertFalse(in_array($collection, $mongo->getCollectionNames()->toArray()));

        // Test isMongoId()
        $id = '12345678absdfgrtuierwe12';
        $this->assertFalse($mongo->isMongoId($id));

        $id = 'aaaabbbbcccc 11122223333';
        $this->assertFalse($mongo->isMongoId($id));

        $id = '543c1d846803fa76058b458b';
        $this->assertTrue($mongo->isMongoId($id));
    }

    public function driverSet()
    {
        Mongo::setConfig(realpath(__DIR__ . '/' . self::CONFIG));

        return [
            [$this->mongo()]
        ];
    }

}