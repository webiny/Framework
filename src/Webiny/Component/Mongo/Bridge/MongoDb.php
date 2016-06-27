<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Mongo\Bridge;

use InvalidArgumentException;
use MongoDB\BSON\ObjectID;
use MongoDB\BulkWriteResult;
use MongoDB\Client;
use MongoDB\Database;
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

    /**
     * @var Database
     */
    private $db;

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
     * Select database
     *
     * @param string $database
     */
    public function selectDatabase($database)
    {
        $this->db = $this->connection->selectDatabase($database);
    }


    /**
     * Create a mongo ID instance
     *
     * @param null|string $id
     *
     * @return mixed
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
     * Check if given string/object is a valid mongo ID.
     *
     * @param mixed $id
     *
     * @return bool
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
     * @param string $collectionName
     * @param array  $pipeline
     * @param array  $options
     *
     * @return Traversable
     */
    public function aggregate($collectionName, array $pipeline, array $options = [])
    {
        return $this->getCollection($collectionName)->aggregate($pipeline, $options);
    }

    /**
     * @param string $collectionName
     * @param array  $operations
     * @param array  $options
     *
     * @return BulkWriteResult
     */
    public function bulkWrite($collectionName, array $operations, array $options = [])
    {
        return $this->getCollection($collectionName)->bulkWrite($operations, $options);
    }

    /**
     * @param string $collectionName
     * @param array  $filter
     * @param array  $options
     *
     * @return int
     */
    public function count($collectionName, $filter = [], array $options = [])
    {
        return $this->getCollection($collectionName)->count($filter, $options);
    }

    /**
     * @param string       $collectionName
     * @param array|object $key
     * @param array        $options
     *
     * @return string
     */
    public function createIndex($collectionName, $key, array $options = [])
    {
        return $this->getCollection($collectionName)->createIndex($key, $options);
    }

    /**
     * @param string $collectionName
     * @param array  $indexes
     *
     * @return \string[]
     */
    public function createIndexes($collectionName, array $indexes)
    {
        return $this->getCollection($collectionName)->createIndexes($indexes);
    }

    /**
     * @param string       $collectionName
     * @param array|object $filter
     * @param array        $options
     *
     * @return DeleteResult
     */
    public function delete($collectionName, $filter, array $options = [])
    {
        return $this->getCollection($collectionName)->deleteMany($filter, $options);
    }

    /**
     * @param string $collectionName
     * @param string $fieldName
     * @param array  $filter
     * @param array  $options
     *
     * @return \mixed[]
     */
    public function distinct($collectionName, $fieldName, $filter = [], array $options = [])
    {
        return $this->getCollection($collectionName)->distinct($fieldName, $filter, $options);
    }

    /**
     * @param string $collectionName
     * @param array  $options
     *
     * @return object
     */
    public function createCollection($collectionName, array $options = [])
    {
        return $this->getDb()->createCollection($collectionName, $options);
    }

    /**
     * @param string $collectionName
     * @param array  $options
     *
     * @return array|object
     */
    public function dropCollection($collectionName, array $options = [])
    {
        return $this->getDb()->dropCollection($collectionName, $options);
    }

    /**
     * @param string $collectionName
     * @param string $indexName
     * @param array  $options
     *
     * @return array|object
     */
    public function dropIndex($collectionName, $indexName, array $options = [])
    {
        return $this->getCollection($collectionName)->dropIndex($indexName, $options);
    }

    /**
     * @param string $collectionName
     * @param array  $options
     *
     * @return array|object
     */
    public function dropIndexes($collectionName, array $options = [])
    {
        return $this->getCollection($collectionName)->dropIndexes($options);
    }

    /**
     * @param string $collectionName
     * @param array  $filter
     * @param array  $options
     *
     * @return Cursor
     */
    public function find($collectionName, $filter = [], array $options = [])
    {
        return $this->getCollection($collectionName)->find($filter, $options);
    }

    /**
     * @param string $collectionName
     * @param array  $filter
     * @param array  $options
     *
     * @return null|object
     */
    public function findOne($collectionName, $filter = [], array $options = [])
    {
        return $this->getCollection($collectionName)->findOne($filter, $options);
    }

    /**
     * @param string       $collectionName
     * @param array|object $filter
     * @param array        $options
     *
     * @return null|object
     */
    public function findOneAndDelete($collectionName, $filter, array $options = [])
    {
        return $this->getCollection($collectionName)->findOneAndDelete($filter, $options);
    }

    /**
     * @param string       $collectionName
     * @param array|object $filter
     * @param array|object $replacement
     * @param array        $options
     *
     * @return null|object
     */
    public function findOneAndReplace($collectionName, $filter, $replacement, array $options = [])
    {
        return $this->getCollection($collectionName)->findOneAndReplace($filter, $replacement, $options);
    }

    /**
     * @param string       $collectionName
     * @param array|object $filter
     * @param array|object $update
     * @param array        $options
     *
     * @return null|object
     */
    public function findOneAndUpdate($collectionName, $filter, $update, array $options = [])
    {
        return $this->getCollection($collectionName)->findOneAndUpdate($filter, $update, $options);
    }

    /**
     * @param string $collectionName
     *
     * @return string
     */
    public function getNamespace($collectionName)
    {
        return $this->getCollection($collectionName)->getNamespace();
    }

    /**
     * @param string $collectionName
     * @param array  $documents
     * @param array  $options
     *
     * @return InsertManyResult
     */
    public function insertMany($collectionName, array $documents, array $options = [])
    {
        return $this->getCollection($collectionName)->insertMany($documents, $options);
    }

    /**
     * @param string       $collectionName
     * @param array|object $document
     * @param array        $options
     *
     * @return InsertOneResult
     */
    public function insertOne($collectionName, $document, array $options = [])
    {
        return $this->getCollection($collectionName)->insertOne($document, $options);
    }

    /**
     * @param array $options
     *
     * @return \MongoDB\Model\CollectionInfoIterator
     */
    public function listCollections(array $options = [])
    {
        return $this->getDb()->listCollections($options);
    }

    /**
     * @param string $collectionName
     * @param array  $options
     *
     * @return IndexInfoIterator
     */
    public function listIndexes($collectionName, array $options = [])
    {
        return $this->getCollection($collectionName)->listIndexes($options);
    }

    /**
     * @param string       $collectionName
     * @param array|object $filter
     * @param array|object $update
     * @param array        $options
     *
     * @return UpdateResult
     */
    public function update($collectionName, $filter, $update, array $options = [])
    {
        return $this->getCollection($collectionName)->updateMany($filter, $update, $options);
    }

    /**
     * @param array|object $command
     * @param array        $options
     *
     * @return Cursor
     * @throws MongoException
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