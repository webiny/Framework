<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Mongo\Bridge;

use InvalidArgumentException;
use MongoDB\BulkWriteResult;
use MongoDB\Client;
use MongoDB\DeleteResult;
use MongoDB\Driver\Cursor;
use MongoDB\InsertManyResult;
use MongoDB\InsertOneResult;
use MongoDB\Model\IndexInfoIterator;
use MongoDB\UpdateResult;
use Traversable;
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

    public function connect($uri, array $uriOptions = [], array $driverOptions = [])
    {
        $server = 'mongodb://' . $uri;
        try {
            $this->connection = new Client($server, $uriOptions, $driverOptions);
        } catch (InvalidArgumentException $e) {
            throw new MongoException($e->getMessage());
        }
    }

    public function aggregate($collectionName, array $pipeline, array $options = [])
    {
        // TODO: Implement aggregate() method.
    }

    public function bulkWrite($collectionName, array $operations, array $options = [])
    {
        // TODO: Implement bulkWrite() method.
    }

    public function count($collectionName, $filter = [], array $options = [])
    {
        // TODO: Implement count() method.
    }

    public function createIndex($collectionName, $key, array $options = [])
    {
        // TODO: Implement createIndex() method.
    }

    public function createIndexes($collectionName, array $indexes)
    {
        // TODO: Implement createIndexes() method.
    }

    public function delete($collectionName, $filter, array $options = [])
    {
        // TODO: Implement delete() method.
    }

    public function distinct($collectionName, $fieldName, $filter = [], array $options = [])
    {
        // TODO: Implement distinct() method.
    }

    public function dropCollection($collectionName, array $options = [])
    {
        // TODO: Implement dropCollection() method.
    }

    public function dropIndex($collectionName, $indexName, array $options = [])
    {
        // TODO: Implement dropIndex() method.
    }

    public function dropIndexes($collectionName, array $options = [])
    {
        // TODO: Implement dropIndexes() method.
    }

    public function find($collectionName, $filter = [], $sort = [], $limit = 0, $skip = 0, array $options = [])
    {
        // TODO: Implement find() method.
    }

    public function findOne($collectionName, $filter = [], array $options = [])
    {
        // TODO: Implement findOne() method.
    }

    public function findOneAndDelete($collectionName, $filter, array $options = [])
    {
        // TODO: Implement findOneAndDelete() method.
    }

    public function findOneAndReplace($collectionName, $filter, $replacement, array $options = [])
    {
        // TODO: Implement findOneAndReplace() method.
    }

    public function findOneAndUpdate($collectionName, $filter, $update, array $options = [])
    {
        // TODO: Implement findOneAndUpdate() method.
    }

    public function getNamespace($collectionName)
    {
        // TODO: Implement getNamespace() method.
    }

    public function insertMany($collectionName, array $documents, array $options = [])
    {
        // TODO: Implement insertMany() method.
    }

    public function insertOne($collectionName, $document, array $options = [])
    {
        // TODO: Implement insertOne() method.
    }

    public function listIndexes($collectionName, array $options = [])
    {
        // TODO: Implement listIndexes() method.
    }

    public function update($collectionName, $filter, $update, array $options = [])
    {
        // TODO: Implement update() method.
    }
}