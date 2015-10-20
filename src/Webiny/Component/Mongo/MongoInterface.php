<?php

namespace Webiny\Component\Mongo;

/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */
interface MongoInterface
{

    public function connect($host, $database, $user = null, $password = null, array $options = []);

    /**
     * Get database collection names
     *
     * @param bool $includeSystemCollections
     *
     * @return array
     */
    public function getCollectionNames($includeSystemCollections = false);

    /**
     * Insert data into collection<br>
     * Returns an array containing the status of the insertion if the "w" option is set.<br>
     * Otherwise, returns TRUE if the inserted array is not empty (a MongoException will be thrown if the inserted array is empty).
     *
     * @param string $collectionName
     * @param array  $data
     * @param array  $options options
     *
     * @return array|bool
     */
    public function insert($collectionName, array $data, $options = []);

    /**
     * Performs an operation similar to SQL's GROUP BY command
     *
     * @param string $collectionName collection name
     * @param array  $keys keys
     * @param array  $initial initial
     * @param array  $reduce reduce
     * @param array  $condition condition
     *
     * @see http://php.net/manual/en/mongocollection.group.php
     *
     * @return array
     */
    public function group($collectionName, $keys, array $initial, $reduce, array $condition = []);


    /**
     * Ensure index<br>
     * Returns an array containing the status of the index creation.
     *
     * @param string $collectionName name
     * @param string $keys keys
     * @param array  $options options
     *
     * @return array
     */
    public function ensureIndex($collectionName, $keys, $options = []);

    /**
     * Get reference
     *
     * @param array $ref ref
     *
     * @return \MongoDBRef
     */
    public function getReference(array $ref);

    /**
     * Get collection indexes
     *
     * @param string $collectionName Collection name
     *
     * @return array
     */
    public function getIndexInfo($collectionName);

    /**
     * Delete index from given collection
     *
     * @param string $collectionName Collection name
     * @param string $index Index name
     *
     * @return mixed
     */
    public function deleteIndex($collectionName, $index);

    /**
     * Delete all indexes from given collection
     *
     * @param string $collectionName Collection name
     *
     * @return array
     */
    public function deleteAllIndexes($collectionName);

    /**
     * Execute JavaScript code on the database server.<br>
     * Returns result of the evaluation.
     *
     * @param string $code code
     * @param array  $args array
     *
     * @see http://php.net/manual/en/mongodb.execute.php
     *
     * @return mixed
     */
    public function execute($code, array $args = []);

    /**
     * Find
     *
     * @param string $collectionName collection name
     * @param array  $query query
     * @param array  $fields fields
     *
     * @return MongoCursor
     */
    public function find($collectionName, array $query = [], array $fields = []);

    /**
     * Create collection
     *
     * @param string $name name
     * @param bool   $capped Enables a capped collection. To create a capped collection, specify true. If you specify true, you must also set a maximum size in the size field.
     * @param int    $size Specifies a maximum size in bytes for a capped collection. The size field is required for capped collections. If capped is false, you can use this field to preallocate space for an ordinary collection.
     * @param int    $max The maximum number of documents allowed in the capped collection. The size limit takes precedence over this limit. If a capped collection reaches its maximum size before it reaches the maximum number of documents, MongoDB removes old documents. If you prefer to use this limit, ensure that the size limit, which is required, is sufficient to contain the documents limit.
     *
     * @return array
     */
    public function createCollection($name, $capped = false, $size = 0, $max = 0);

    /**
     * Drop collection<br>
     * Returns the database response.
     * <code>
     * Array
     *   (
     *       [nIndexesWas] => 1
     *       [msg] => all indexes deleted for collection
     *       [ns] => my_db.articles
     *       [ok] => 1
     *   )
     * </code>
     *
     * @param $collectionName
     *
     * @return array
     */
    public function dropCollection($collectionName);

    /**
     * Execute Mongo command
     *
     * @param array $data data
     *
     * @see http://php.net/manual/en/mongodb.command.php
     *
     * @return string|null
     */
    public function command(array $data);

    /**
     * Returns an array of distinct values, or FALSE on failure
     *
     * @param string $collectionName
     * @param string $key
     * @param array  $query
     *
     * @return array|false
     */
    public function distinct($collectionName, $key, array $query = null);

    /**
     * Find one<br>
     * Returns array of data or NULL if not found.
     *
     * @param string $collectionName collection name
     * @param array  $query query
     * @param array  $fields fields
     *
     * @return array|null
     */
    public function findOne($collectionName, array $query = [], array $fields = []);

    /**
     * Returns number of documents in given collection by given criteria.
     *
     * @param string $collectionName collection name
     * @param array  $query query
     *
     * @return int
     */
    public function count($collectionName, array $query = []);

    /**
     * Remove documents from collection by given criteria.<br>
     * Returns array containing result of remove operation.
     *
     * <code>
     * Array
     *   (
     *       [n] => 1
     *       [connectionId] => 61
     *       [err] =>
     *       [ok] => 1
     *   )
     *
     * </code>
     *
     * @param string $collectionName collection name
     * @param array  $criteria criteria
     * @param array  $options options
     *
     * @return array
     */
    public function remove($collectionName, array $criteria, $options = []);

    /**
     * Insert or update existing record<br>
     * If `w` was set, returns an array containing the status of the save.<br>
     * Otherwise, returns a boolean representing if the array was not empty (an empty array will not be inserted).
     *
     * @param string $collectionName collection name
     * @param array  $data data
     * @param array  $options options
     *
     * @return array|bool
     */
    public function save($collectionName, array $data, $options = []);

    /**
     * Update document<br>
     * Returns array containing result of update operation.
     * <code>
     * Array
     *   (
     *       [updatedExisting] => 1
     *       [n] => 1
     *       [connectionId] => 67
     *       [err] =>
     *       [ok] => 1
     *   )
     * </code>
     *
     * @param string $collectionName collection name
     * @param array  $criteria criteria
     * @param array  $newObj new obj
     * @param array  $options options
     *
     * @return array
     */
    public function update($collectionName, array $criteria, array $newObj, $options = []);

    /**
     * Aggregate
     *
     * @param string $collectionName
     * @param array  $pipelines
     *
     * @return mixed
     */
    public function aggregate($collectionName, array $pipelines);
}