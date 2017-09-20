<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Mongo\Bridge;

use InvalidArgumentException;
use MongoDB\BSON\ObjectID;
use MongoDB\Client;
use MongoDB\Database;
use Webiny\Component\Mongo\MongoException;
use Webiny\Component\StdLib\StdLibTrait;


/**
 * Database
 * @package Webiny\Component\Mongo\Bridge
 */
class MongoDb implements MongoInterface
{
    use StdLibTrait;

    /**
     * @var Client
     */
    private $connection;

    /**
     * @var Database
     */
    private $db;

    /**
     * @inheritdoc
     */
    public function connect($uri, array $uriOptions = [], array $driverOptions = [])
    {
        $server = 'mongodb://' . $uri;
        try {
            $this->connection = new Client($server, $uriOptions, $driverOptions);
        } catch (InvalidArgumentException $e) {
            throw new MongoException($e->getMessage());
        }
    }

    /**
     * @inheritdoc
     */
    public function selectDatabase($database)
    {
        $this->db = $this->connection->selectDatabase($database);
    }


    /**
     * @inheritdoc
     */
    public function id($id = null)
    {
        $args = [];
        if ($id instanceof ObjectID) {
            return $id;
        }

        if ($id !== null) {
            $args[] = $id;
        }

        return new ObjectID(...$args);
    }

    /**
     * @inheritdoc
     */
    public function isId($id)
    {
        if (!$id) {
            return false;
        }

        if ($id instanceof ObjectID) {
            return true;
        }

        try {
            new ObjectID($id);
        } catch (\MongoDB\Driver\Exception\InvalidArgumentException $e) {
            return false;
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function aggregate($collectionName, array $pipeline, array $options = [])
    {
        return $this->getCollection($collectionName)->aggregate($pipeline, $options);
    }

    /**
     * @inheritdoc
     */
    public function bulkWrite($collectionName, array $operations, array $options = [])
    {
        return $this->getCollection($collectionName)->bulkWrite($operations, $options);
    }

    /**
     * @inheritdoc
     */
    public function count($collectionName, $filter = [], array $options = [])
    {
        return $this->getCollection($collectionName)->count($filter, $options);
    }

    /**
     * @inheritdoc
     */
    public function createIndex($collectionName, $key, array $options = [])
    {
        return $this->getCollection($collectionName)->createIndex($key, $options);
    }

    /**
     * @inheritdoc
     */
    public function createIndexes($collectionName, array $indexes)
    {
        return $this->getCollection($collectionName)->createIndexes($indexes);
    }

    /**
     * @inheritdoc
     */
    public function delete($collectionName, $filter, array $options = [])
    {
        return $this->getCollection($collectionName)->deleteMany($filter, $options);
    }

    /**
     * @inheritdoc
     */
    public function distinct($collectionName, $fieldName, $filter = [], array $options = [])
    {
        return $this->getCollection($collectionName)->distinct($fieldName, $filter, $options);
    }

    /**
     * @inheritdoc
     */
    public function createCollection($collectionName, array $options = [])
    {
        return $this->getDb()->createCollection($collectionName, $options);
    }

    /**
     * @inheritdoc
     */
    public function dropCollection($collectionName, array $options = [])
    {
        return $this->getDb()->dropCollection($collectionName, $options);
    }

    /**
     * @inheritdoc
     */
    public function dropIndex($collectionName, $indexName, array $options = [])
    {
        return $this->getCollection($collectionName)->dropIndex($indexName, $options);
    }

    /**
     * @inheritdoc
     */
    public function dropIndexes($collectionName, array $options = [])
    {
        return $this->getCollection($collectionName)->dropIndexes($options);
    }

    /**
     * @inheritdoc
     */
    public function find($collectionName, $filter = [], array $options = [])
    {
        return $this->getCollection($collectionName)->find($filter, $options)->toArray();
    }

    /**
     * @inheritdoc
     */
    public function findOne($collectionName, $filter = [], array $options = [])
    {
        return $this->getCollection($collectionName)->findOne($filter, $options);
    }

    /**
     * @inheritdoc
     */
    public function findOneAndDelete($collectionName, $filter, array $options = [])
    {
        return $this->getCollection($collectionName)->findOneAndDelete($filter, $options);
    }

    /**
     * @inheritdoc
     */
    public function findOneAndReplace($collectionName, $filter, $replacement, array $options = [])
    {
        return $this->getCollection($collectionName)->findOneAndReplace($filter, $replacement, $options);
    }

    /**
     * @inheritdoc
     */
    public function findOneAndUpdate($collectionName, $filter, $update, array $options = [])
    {
        return $this->getCollection($collectionName)->findOneAndUpdate($filter, $update, $options);
    }

    /**
     * @inheritdoc
     */
    public function getNamespace($collectionName)
    {
        return $this->getCollection($collectionName)->getNamespace();
    }

    /**
     * @inheritdoc
     */
    public function insertMany($collectionName, array $documents, array $options = [])
    {
        return $this->getCollection($collectionName)->insertMany($documents, $options);
    }

    /**
     * @inheritdoc
     */
    public function insertOne($collectionName, $document, array $options = [])
    {
        return $this->getCollection($collectionName)->insertOne($document, $options);
    }

    /**
     * @inheritdoc
     */
    public function listCollections(array $options = [])
    {
        return iterator_to_array($this->getDb()->listCollections($options));
    }

    /**
     * @inheritdoc
     */
    public function listIndexes($collectionName, array $options = [])
    {
        return iterator_to_array($this->getCollection($collectionName)->listIndexes($options));
    }

    /**
     * @inheritdoc
     */
    public function update($collectionName, $filter, $update, array $options = [])
    {
        return $this->getCollection($collectionName)->updateMany($filter, $update, $options);
    }

    /**
     * @inheritdoc
     */
    public function command($command, array $options = [])
    {
        return $this->getDb()->command($command, $options);
    }

    /**
     * @return Database
     * @throws MongoException
     */
    private function getDb()
    {
        if (!$this->db) {
            throw new MongoException('No database selected!');
        }

        return $this->db;
    }

    /**
     * @param $collectionName
     *
     * @return \MongoDB\Collection
     * @throws MongoException
     */
    private function getCollection($collectionName)
    {
        return $this->getDb()->selectCollection($collectionName);
    }
}