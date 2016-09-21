<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Entity\Tests;

use MongoDB\Model\CollectionInfo;
use PHPUnit_Framework_TestCase;
use Webiny\Component\Entity\AbstractEntity;
use Webiny\Component\Entity\Attribute\Many2ManyAttribute;
use Webiny\Component\Entity\Attribute\Validation\ValidationException;
use Webiny\Component\Entity\Entity;
use Webiny\Component\Entity\EntityException;
use Webiny\Component\Entity\EntityTrait;
use Webiny\Component\Entity\Tests\Lib as Lib;
use Webiny\Component\Entity\Tests\Lib\NoValidation\Many2Many;
use Webiny\Component\Entity\Tests\lib\NoValidation\Many2One;
use Webiny\Component\Mongo\Mongo;
use Webiny\Component\Mongo\MongoTrait;
use Webiny\Component\StdLib\StdObject\ArrayObject\ArrayObject;

class EntityTest extends PHPUnit_Framework_TestCase
{
    use EntityTrait, MongoTrait;

    const CONFIG = '/ExampleConfig.yaml';
    const MONGO_CONFIG = '/MongoConfig.yaml';

    public static function setUpBeforeClass()
    {
        Mongo::setConfig(realpath(__DIR__ . '/' . self::MONGO_CONFIG));
        Entity::setConfig(realpath(__DIR__ . '/' . self::CONFIG));

        self::deleteAllTestCollections();
    }

    public static function tearDownAfterClass()
    {
        //self::deleteAllTestCollections();
    }

    /**
     * This test runs a simple populate on entity with no validators.
     *
     * It tests:
     * - all attribute types
     * - all reference attributes: many2one, one2many and many2many
     * - it tests both on-fly creation and linking with existing entities
     */
    public function testPopulateNoValidationAllNew()
    {
        $many2one = new Many2One();
        $many2one->char = 'many2oneExisting';
        $many2one->save();

        $many2many = new Many2Many();
        $many2many->char = 'many2many1';
        $many2many->save();

        $many2many2 = new Many2Many();
        $many2many2->char = 'many2many2';
        $many2many2->save();

        $data = [
            'boolean'          => true,
            'char'             => 'char',
            'skip'             => 'this value will not be set',
            'integer'          => 12,
            'calculation'      => 5,
            'float'            => 20.35,
            'date'             => '2016-03-14',
            'datetime'         => '2016-03-14T13:45:20+0000',
            'arr'              => [1, 2, 3],
            'object'           => [
                'key1' => 'value',
                'key2' => 12
            ],
            'geoPoint'         => [
                'lat'          => 50,
                'lng'          => 100,
                'stripThisKey' => 'whatever'
            ],
            'many2oneNew'      => [
                'char' => 'many2oneNew'
            ],
            'many2oneExisting' => $many2one,
            'one2many'         => [
                [
                    'char' => 'one2many1'
                ]
            ],
            'many2many'        => [
                $many2many->id, // Store using simple ID
                $many2many2 // Store using entity instance
            ]
        ];

        $class = Lib\Classes::ENTITY_NO_VALIDATION;
        /* @var AbstractEntity $entity */
        $entity = new $class;
        $entity->populate($data);
        $entity->one2many[] = new Lib\NoValidation\One2Many();
        $entity->one2many[1]->char = 'one2many2';
        $entity->save();

        // Test current $entity state
        $this->assertEntityStateNoValidation($entity);

        // Load entity from DB and test state again
        $id = $entity->id;
        Entity::getInstance()->remove($entity);
        $entity = $class::findById($id);
        $this->assertEntityStateNoValidation($entity);

        // Append more one2many values after entity is loaded from DB
        $entity->one2many[] = new Lib\NoValidation\One2Many();
        $entity->one2many[2]->char = 'one2many3';
        $this->assertEquals(3, $entity->one2many->count());
        $this->assertEquals(3, count($entity->one2many));

        // Test toArray conversion
        $fields = '*,float:2,arr,object[key1],dynamicWithParams:4,many2oneNew[char,relations.integer],one2many,many2many';
        $array = new ArrayObject($entity->toArray($fields, 2));
        $this->assertEquals('char', $array->keyNested('char'));
        $this->assertEquals(4, $array->keyNested('float'));
        $this->assertArrayNotHasKey('boolean', $array->val());
        $this->assertArrayNotHasKey('skip', $array->val());
        $this->assertEquals([1, 2, 3], $array->keyNested('arr'));
        $this->assertEquals('value', $array->keyNested('object.key1'));
        $this->assertNull($array->keyNested('object.key2'));
        // GeoPoint should return an array of lat/lng values
        $this->assertEquals(50, $array->keyNested('geoPoint.lat'));
        $this->assertEquals(100, $array->keyNested('geoPoint.lng'));
        // If return value of dynamic attribute is AbstractEntity or EntityCollection,
        // EntityDataExtractor should call toArray() an those objects
        $this->assertEquals(24, $array->key('dynamicWithDefaultParams'));
        $this->assertEquals(48, $array->key('dynamicWithParams'));
        $this->assertInternalType('array', $array->key('dynamicEntity'));
        $this->assertInternalType('array', $array->key('dynamicEntityCollection'));
        $this->assertCount(2, $array->key('dynamicEntityCollection'));
        $this->assertEquals('many2oneExisting', $array->keyNested('dynamicEntityCollection.0.char'));
        $this->assertEquals('many2oneNew', $array->keyNested('dynamicEntityCollection.1.char'));
        // GeoPoint attribute should strips all values not related to mongo Point
        $this->assertArrayNotHasKey('stripThisKey', $array->keyNested('geoPoint'));
        $this->assertEquals('many2oneNew', $array->keyNested('many2oneNew.char'));
        $this->assertEquals(12, $array->keyNested('many2oneNew.relations.0.integer'));
        $this->assertEquals('one2many1', $array->keyNested('one2many.0.char'));
        $this->assertEquals('one2many2', $array->keyNested('one2many.1.char'));
        $this->assertEquals('one2many3', $array->keyNested('one2many.2.char'));
        $this->assertEquals('many2many1', $array->keyNested('many2many.0.char'));
        $this->assertEquals('many2many2', $array->keyNested('many2many.1.char'));
    }

    /**
     * @expectedException \Webiny\Component\Entity\EntityException
     * @dataProvider requiredData
     */
    public function testRequiredValidation($data)
    {
        try {
            $entity = new Lib\Validation\EntityRequired();
            $entity->populate($data);
        } catch (EntityException $e) {
            throw $e;
        }
    }

    public function requiredData()
    {
        return include_once __DIR__ . '/Lib/RequiredData.php';
    }

    /**
     * @expectedException \Webiny\Component\Entity\EntityException
     * @dataProvider validationData
     */
    public function testPopulateValidation($data)
    {
        /**
         * This block is only for unique validator.
         * We need to create a record with the same value in order to successfully test unique validator.
         */
        if (isset($data['unique'])) {
            try {
                $entity = new Lib\Validation\Entity();
                $entity->unique = $data['unique'];
                $entity->save();
            } catch (ValidationException $e) {
                // 'unique' attribute already exists
            }
        }

        $entity = new Lib\Validation\Entity();
        $entity->populate($data);
    }

    public function testCustomValidationMessages()
    {
        $entity = new Lib\NoValidation\Entity();
        $entity->getAttribute('char')->setValidators('required')->setValidationMessages(['required' => 'Custom message']);
        try {
            $entity->populate([]);
        } catch (EntityException $e) {
            $this->assertEquals('Custom message', $e->getInvalidAttributes()['char']);
        }
    }

    public function testCustomValidator()
    {
        $validator = function ($value) {
            throw new ValidationException('Custom validator ' . $value);
        };
        $entity = new Lib\NoValidation\Entity();
        $entity->getAttribute('char')->setValidators([$validator]);
        try {
            $entity->populate(['char' => 'value']);
        } catch (EntityException $e) {
            $this->assertEquals('Custom validator value', $e->getInvalidAttributes()['char']);
        }
    }

    public function validationData()
    {
        return include_once __DIR__ . '/Lib/ValidationData.php';
    }

    /**
     * @expectedException \Webiny\Component\Entity\EntityException
     * @expectedExceptionCode 101
     */
    public function testMissingRequiredValues()
    {
        $entity = new Lib\Validation\EntityRequired();
        $entity->boolean = true;
        $entity->save();
    }

    private function assertEntityStateNoValidation($entity)
    {
        $this->assertTrue($entity->boolean);
        $this->assertEquals('char', $entity->char);
        $this->assertNull($entity->skip);
        $this->assertEquals(12, $entity->integer);
        $this->assertEquals(5, $entity->calculation);
        $this->assertEquals(5, $entity->calculation());
        $this->assertEquals(10, $entity->calculation(2));
        $this->assertEquals(20.35, $entity->float);
        $this->assertEquals('2016-03-14', $entity->date);
        $this->assertEquals('2016-03-14T13:45:20+0000', $entity->datetime);
        $this->assertEquals('dynamic-value', $entity->dynamic);
        $this->assertEquals(24, $entity->dynamicWithDefaultParams);
        $this->assertEquals(48, $entity->dynamicWithParams(4));
        $this->assertEquals('dynamic-value-db', $entity->dynamicDb);
        $this->assertEquals([1, 2, 3], $entity->arr->val());
        $this->assertEquals([
            'key1' => 'value',
            'key2' => 12
        ], $entity->object->val());
        $this->assertEquals(50, $entity->geoPoint['lat']);
        $this->assertEquals(100, $entity->geoPoint['lng']);
        $this->assertArrayNotHasKey('stripThisKey', $entity->geoPoint);
        $this->assertEquals('many2oneNew', $entity->many2oneNew->char);
        $this->assertEquals('many2oneExisting', $entity->many2oneExisting->char);
        $this->assertEquals(2, $entity->one2many->count());
        $this->assertEquals('one2many1', $entity->one2many[0]->char);
        $this->assertEquals('one2many2', $entity->one2many[1]->char);
        $this->assertEquals(2, $entity->many2many->count());
        $this->assertEquals('many2many1', $entity->many2many[0]->char);
        $this->assertEquals('many2many2', $entity->many2many[1]->char);
    }

    /**
     * Test entity finding
     */
    public function testFind()
    {
        $entity = new Lib\NoValidation\Entity();
        $entity->char = 'Webiny Test';
        $entity->save();

        $id = $entity->id;

        // Clear entity instance cache
        Entity::getInstance()->reset();

        $entity = Lib\NoValidation\Entity::findOne(['char' => 'Webiny Test']);
        $this->assertInstanceOf('Webiny\Component\Entity\AbstractEntity', $entity);

        $entity = Lib\NoValidation\Entity::findOne(['char' => 'NO TITLE']);
        $this->assertNull($entity);

        $entity = Lib\NoValidation\Entity::findById($id);
        $this->assertInstanceOf('Webiny\Component\Entity\AbstractEntity', $entity);

        $collection = Lib\NoValidation\Entity::find(['char' => 'Webiny Test']);
        $this->assertEquals(1, $collection->count());
    }

    /**
     * Test delete on parent entity when one2many attribute has a 'restrict' flag - the delete action on parent should not be permitted.
     *
     * @expectedException \Webiny\Component\Entity\EntityException
     */
    public function testRestrictException()
    {
        $entity = Lib\NoValidation\Entity::findOne(['char' => 'Webiny Test']);
        $this->assertInstanceOf('Webiny\Component\Entity\AbstractEntity', $entity);
        $entity->one2many = [
            ['char' => 'first']
        ];
        $entity->save();

        $entity->getAttribute('one2many')->setOnDelete('restrict');
        $entity->delete();
    }

    /**
     * Test cascade deletion of one2many attribute values when parent entity is deleted
     */
    public function testCascade()
    {
        $entity = Lib\NoValidation\Entity::findOne(['char' => 'Webiny Test']);
        $entity->getAttribute('one2many')->setOnDelete('cascade');
        $entity->delete();

        $one2many = Lib\Classes::ONE_2_MANY_NO_VALIDATION;
        $this->assertNull($one2many::findOne(['char' => 'first']));
    }

    /**
     * Test update protection on attribute - if set, new value should not be assigned after initial value was set.
     */
    public function testSetOnce()
    {
        $entity = new Lib\NoValidation\Entity();
        $entity->char = 'Initial title';
        $entity->save();

        // Disable update of 'char' attribute
        $entity->getAttribute('char')->setOnce()->setValue('New title');
        $this->assertEquals('Initial title', $entity->char);

        $entity->populate(['char' => 'Some title']);
        $this->assertEquals('Initial title', $entity->char);

        // Enable update of 'title' attribute
        $entity->getAttribute('char')->setOnce(false)->setValue('New title');
        $this->assertEquals('New title', $entity->char);
    }

    /**
     * Test default value of the attribute
     */
    public function testDefaultValue()
    {
        $entity = new Lib\NoValidation\Entity();
        $entity->getAttribute('char')->setDefaultValue('Default Title');
        $entity->save();

        Entity::getInstance()->reset();

        $entity = Lib\NoValidation\Entity::findOne(['char' => 'Default Title']);
        $this->assertInstanceOf('Webiny\Component\Entity\AbstractEntity', $entity);
    }

    public function testMany2Many()
    {
        $entity = new Lib\NoValidation\Entity();
        $entity->char = 'Webiny Test';
        // Set initial value
        $entity->many2many = [
            ['char' => 'many1']
        ];
        // Append another value
        $entity->many2many[] = ['char' => 'many2'];
        $this->assertCount(2, $entity->many2many);
        $entity->save();
        $id = $entity->id;

        // Reload entity and assert values are saved
        Entity::getInstance()->reset();
        $entity = Lib\NoValidation\Entity::findById($id);
        $this->assertCount(2, $entity->many2many);

        // Test simple "unset"
        unset($entity->many2many[0]);
        $this->assertCount(1, $entity->many2many);
        // Assert that the correct index was unset
        $this->assertEquals('many2', $entity->many2many[1]->char);

        // Test appending of entity instances
        $many3 = new Many2Many();
        $many3->populate(['char' => 'many3']);
        $entity->many2many[] = ['char' => 'many2'];
        $entity->many2many[] = $many3;
        $entity->save();

        // Reload and test removal of single and all values
        Entity::getInstance()->reset();
        $entity = Lib\NoValidation\Entity::findById($id);
        $this->assertCount(3, $entity->many2many);
        $entity->getAttribute('many2many')->unlink($many3);
        $this->assertCount(2, $entity->many2many);
        $entity->getAttribute('many2many')->unlinkAll();
        $this->assertCount(0, $entity->many2many);
        $entity->save();

        // Reload and assert no values are saved
        Entity::getInstance()->reset();
        $entity = Lib\NoValidation\Entity::findById($id);
        $this->assertCount(0, $entity->many2many);

        // Append 2 values, save, reload and try setting an empty array to remove all values
        $entity->many2many[] = ['char' => 'many1'];
        $entity->many2many[] = ['char' => 'many2'];
        $this->assertCount(2, $entity->many2many);
        $entity->save();

        Entity::getInstance()->reset();
        $entity = Lib\NoValidation\Entity::findById($id);
        // Remove all items by setting an empty array
        $entity->many2many = [];
        $this->assertCount(0, $entity->many2many);
        $entity->save();

        // Reload and make sure nothing was loaded from DB
        Entity::getInstance()->reset();
        $entity = Lib\NoValidation\Entity::findById($id);
        $this->assertCount(0, $entity->many2many);
    }

    /**
     * Test onSet, onGet, onToDb and onToArray callbacks as well as setAfterPopulate (only useful in combination with onSet callback)
     */
    public function testOnCallbacks()
    {
        $entity = new Lib\EntityOnCallbacks();
        $entity->populate(['char' => 'value', 'number' => 12])->save();
        $this->assertEquals('get-db-get-set-12-value', $entity->char);

        Entity::getInstance()->reset();

        $entity = Lib\EntityOnCallbacks::findOne(['number' => 12]);
        $this->assertEquals('get-db-get-set-12-value', $entity->char);
        $this->assertEquals(120, $entity->number);

        $array = $entity->toArray();
        $this->assertEquals(['key' => 'value'], $array['char']);
    }

    private static function deleteAllTestCollections()
    {
        /* @var $collection CollectionInfo */
        foreach (self::mongo()->listCollections() as $collection) {
            if (strpos($collection->getName(), 'Entity_') === 0) {
                self::mongo()->dropCollection(substr($collection->getName(), 7));
            }
        }
    }
}