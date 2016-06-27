<?php

namespace Webiny\Component\Mongo\Bridge;

use InvalidArgumentException;
use MongoDB\BulkWriteResult;
use MongoDB\DeleteResult;
use MongoDB\Driver\Cursor;
use MongoDB\InsertManyResult;
use MongoDB\InsertOneResult;
use MongoDB\Model\IndexInfoIterator;
use MongoDB\UpdateResult;
use Traversable;

/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */
interface MongoInterface
{

    /**
     * Connect to Mongo instance
     *
     * @param       $uri
     * @param array $uriOptions
     * @param array $driverOptions
     *
     * @return mixed
     */
    public function connect($uri, array $uriOptions = [], array $driverOptions = []);

    /**
     * Select database
     *
     * @param string $database
     */
    public function selectDatabase($database);

    /**
     * Create a mongo ID instance
     *
     * @param null|string $id
     *
     * @return mixed
     */
    public function id($id = null);

    /**
     * Check if given string/object is a valid mongo ID.
     *
     * @param mixed $id
     *
     * @return bool
     */
    public function isId($id);

    /**
     * Executes an aggregation framework pipeline on the collection.
     *
     * Note: this method's return value depends on the MongoDB server version
     * and the "useCursor" option. If "useCursor" is true, a Cursor will be
     * returned; otherwise, an ArrayIterator is returned, which wraps the
     * "result" array from the command response document.
     *
     * Note: BSON deserialization of inline aggregation results (i.e. not using
     * a command cursor) does not yet support a custom type map
     * (depends on: https://jira.mongodb.org/browse/PHPC-314).
     *
     *
     * @param string $collectionName
     * @param array  $pipeline List of pipeline operations
     * @param array  $options Command options
     *
     * @return Traversable
     */
    public function aggregate($collectionName, array $pipeline, array $options = []);

    /**
     * Executes multiple write operations.
     *
     *
     * @param string  $collectionName
     * @param array[] $operations List of write operations
     * @param array   $options Command options
     *
     * @return BulkWriteResult
     */
    public function bulkWrite($collectionName, array $operations, array $options = []);

    /**
     * Gets the number of documents matching the filter.
     *
     *
     * @param string       $collectionName
     * @param array|object $filter Query by which to filter documents
     * @param array        $options Command options
     *
     * @return integer
     */
    public function count($collectionName, $filter = [], array $options = []);

    /**
     * Create a single index for the collection.
     *
     *
     * @param string       $collectionName
     * @param array|object $key Document containing fields mapped to values,
     *                              which denote order or an index type
     * @param array        $options Index options
     *
     * @return string The name of the created index
     */
    public function createIndex($collectionName, $key, array $options = []);

    /**
     * Create one or more indexes for the collection.
     *
     * Each element in the $indexes array must have a "key" document, which
     * contains fields mapped to an order or type. Other options may follow.
     * For example:
     *
     *     $indexes = [
     *         // Create a unique index on the "username" field
     *         [ 'key' => [ 'username' => 1 ], 'unique' => true ],
     *         // Create a 2dsphere index on the "loc" field with a custom name
     *         [ 'key' => [ 'loc' => '2dsphere' ], 'name' => 'geo' ],
     *     ];
     *
     * If the "name" option is unspecified, a name will be generated from the
     * "key" document.
     *
     * @see http://docs.mongodb.org/manual/reference/command/createIndexes/
     * @see http://docs.mongodb.org/manual/reference/method/db.collection.createIndex/
     *
     * @param string  $collectionName
     * @param array[] $indexes List of index specifications
     *
     * @return string[] The names of the created indexes
     * @throws InvalidArgumentException if an index specification is invalid
     */
    public function createIndexes($collectionName, array $indexes);

    /**
     * Deletes all documents matching the filter.
     *
     * @see http://docs.mongodb.org/manual/reference/command/delete/
     *
     * @param string       $collectionName
     * @param array|object $filter Query by which to delete documents
     * @param array        $options Command options
     *
     * @return DeleteResult
     */
    public function delete($collectionName, $filter, array $options = []);

    /**
     * Finds the distinct values for a specified field across the collection.
     *
     *
     * @param string       $collectionName
     * @param string       $fieldName Field for which to return distinct values
     * @param array|object $filter Query by which to filter documents
     * @param array        $options Command options
     *
     * @return mixed[]
     */
    public function distinct($collectionName, $fieldName, $filter = [], array $options = []);

    /**
     * Create collection.
     *
     * @param string $collectionName
     * @param array  $options Additional options
     *
     * @return array|object Command result document
     */
    public function createCollection($collectionName, array $options = []);

    /**
     * Drop this collection.
     *
     * @param string $collectionName
     * @param array  $options Additional options
     *
     * @return array|object Command result document
     */
    public function dropCollection($collectionName, array $options = []);

    /**
     * Drop a single index in the collection.
     *
     * @param string $collectionName
     * @param string $indexName Index name
     * @param array  $options Additional options
     *
     * @return array|object Command result document
     * @throws InvalidArgumentException if $indexName is an empty string or "*"
     */
    public function dropIndex($collectionName, $indexName, array $options = []);

    /**
     * Drop all indexes in the collection.
     *
     * @param string $collectionName
     * @param array  $options Additional options
     *
     * @return array|object Command result document
     */
    public function dropIndexes($collectionName, array $options = []);

    /**
     * Finds documents matching the query.
     *
     * @see http://docs.mongodb.org/manual/core/read-operations-introduction/
     *
     * @param string       $collectionName
     * @param array|object $filter Query by which to filter documents
     * @param array        $options Additional options
     *
     * @return Cursor
     */
    public function find($collectionName, $filter = [], array $options = []);

    /**
     * Finds a single document matching the query.
     *
     * @see http://docs.mongodb.org/manual/core/read-operations-introduction/
     *
     * @param string       $collectionName
     * @param array|object $filter Query by which to filter documents
     * @param array        $options Additional options
     *
     * @return object|null
     */
    public function findOne($collectionName, $filter = [], array $options = []);

    /**
     * Finds a single document and deletes it, returning the original.
     *
     * The document to return may be null.
     *
     * Note: BSON deserialization of the returned document does not yet support
     * a custom type map (depends on: https://jira.mongodb.org/browse/PHPC-314).
     *
     * @see http://docs.mongodb.org/manual/reference/command/findAndModify/
     *
     * @param string       $collectionName
     * @param array|object $filter Query by which to filter documents
     * @param array        $options Command options
     *
     * @return object|null
     */
    public function findOneAndDelete($collectionName, $filter, array $options = []);

    /**
     * Finds a single document and replaces it, returning either the original or
     * the replaced document.
     *
     * The document to return may be null. By default, the original document is
     * returned. Specify FindOneAndReplace::RETURN_DOCUMENT_AFTER for the
     * "returnDocument" option to return the updated document.
     *
     * Note: BSON deserialization of the returned document does not yet support
     * a custom type map (depends on: https://jira.mongodb.org/browse/PHPC-314).
     *
     * @see http://docs.mongodb.org/manual/reference/command/findAndModify/
     *
     * @param string       $collectionName
     * @param array|object $filter Query by which to filter documents
     * @param array|object $replacement Replacement document
     * @param array        $options Command options
     *
     * @return object|null
     */
    public function findOneAndReplace($collectionName, $filter, $replacement, array $options = []);

    /**
     * Finds a single document and updates it, returning either the original or
     * the updated document.
     *
     * The document to return may be null. By default, the original document is
     * returned. Specify FindOneAndUpdate::RETURN_DOCUMENT_AFTER for the
     * "returnDocument" option to return the updated document.
     *
     * Note: BSON deserialization of the returned document does not yet support
     * a custom type map (depends on: https://jira.mongodb.org/browse/PHPC-314).
     *
     * @see http://docs.mongodb.org/manual/reference/command/findAndModify/
     *
     * @param string       $collectionName
     * @param array|object $filter Query by which to filter documents
     * @param array|object $update Update to apply to the matched document
     * @param array        $options Command options
     *
     * @return object|null
     */
    public function findOneAndUpdate($collectionName, $filter, $update, array $options = []);

    /**
     * Return the collection namespace.
     *
     * @see http://docs.mongodb.org/manual/faq/developers/#faq-dev-namespace
     *
     * @param string $collectionName
     *
     * @return string
     */
    public function getNamespace($collectionName);

    /**
     * Inserts multiple documents.
     *
     * @see http://docs.mongodb.org/manual/reference/command/insert/
     *
     * @param string           $collectionName
     * @param array[]|object[] $documents The documents to insert
     * @param array            $options Command options
     *
     * @return InsertManyResult
     */
    public function insertMany($collectionName, array $documents, array $options = []);

    /**
     * Inserts one document.
     *
     * @see http://docs.mongodb.org/manual/reference/command/insert/
     *
     * @param string       $collectionName
     * @param array|object $document The document to insert
     * @param array        $options Command options
     *
     * @return InsertOneResult
     */
    public function insertOne($collectionName, $document, array $options = []);

    /**
     * Returns information for all collections.
     *
     * @param array $options
     *
     * @return mixed
     */
    public function listCollections(array $options = []);

    /**
     * Returns information for all indexes for the collection.
     *
     * @param string $collectionName
     * @param array  $options
     *
     * @return IndexInfoIterator
     */
    public function listIndexes($collectionName, array $options = []);

    /**
     * Updates all documents matching the filter.
     *
     * @see http://docs.mongodb.org/manual/reference/command/update/
     *
     * @param string       $collectionName
     * @param array|object $filter Query by which to filter documents
     * @param array|object $update Update to apply to the matched documents
     * @param array        $options Command options
     *
     * @return UpdateResult
     */
    public function update($collectionName, $filter, $update, array $options = []);

    /**
     * Execute a command on this database.
     *
     * @param array|object $command Command document
     * @param array        $options Options for command execution
     * @return Cursor
     * @throws InvalidArgumentException
     */
    public function command($command, array $options = []);
}