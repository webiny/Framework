<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @link      http://www.webiny.com/wf-snv for the canonical source repository
 * @copyright Copyright (c) 2009-2013 Webiny LTD. (http://www.webiny.com)
 * @license   http://www.webiny.com/framework/license
 */

namespace Webiny\Component\Mongo;

use Webiny\Component\Mongo\Index\IndexInterface;
use Webiny\Component\StdLib\ComponentTrait;
use Webiny\Component\StdLib\StdLibTrait;

/**
 * Mongo
 *
 * @package Webiny\Component\Mongo
 */
class Mongo
{
    use ComponentTrait, StdLibTrait;

    /**
     * @var MongoInterface
     */
    private $driver = null;

    private $collectionPrefix = '';

    private $resultClass = '';

    /**
     * Check if given string is a potentially valid MongoId string<br>
     *
     * NOTE: This check is not bullet proof but is useful in most situations.
     *
     * @param $id
     *
     * @return bool
     */
    public static function isMongoId($id)
    {
        if ($id instanceof \MongoId) {
            return true;
        }

        if (!self::isString($id)) {
            return false;
        }
        $match = self::str($id)->match('[0-9a-f]{24}', false);

        if (!$match) {
            return false;
        }

        $mongoId = new \MongoId($match[0]);

        return $mongoId->getTimestamp() > 0;
    }

    public function __construct($host, $database, $user = null, $password = null, $collectionPrefix = '', $options = [])
    {
        $mongoBridge = $this->getConfig()->get('Driver', '\Webiny\Component\Mongo\Driver\Mongo');
        $this->driver = new $mongoBridge();
        $this->driver->connect($host, $database, $user, $password, $options);
        $this->collectionPrefix = $collectionPrefix;

        // Result class
        $baseResultClass = '\Webiny\Component\Mongo\MongoResult';
        $this->resultClass = $this->getConfig()->get('ResultClass', $baseResultClass);

        if ($this->resultClass != $baseResultClass && !$this->isSubClassOf($this->resultClass, $baseResultClass)) {
            throw new MongoException(MongoException::INVALID_RESULT_CLASS_PROVIDED);
        }
    }

    /**
     * Construct Mongo ID
     *
     * @param $id
     *
     * @return \MongoId
     */
    public function id($id)
    {
        return new \MongoId($id);
    }

    /**
     * Create mongo index
     *
     * @param string         $collectionName Target collection
     * @param IndexInterface $index Index object
     *
     * @return MongoResult
     */
    public function createIndex($collectionName, IndexInterface $index)
    {
        $collectionName = $this->collectionPrefix . $collectionName;

        $result = $this->driver->ensureIndex($collectionName, $index->getFields(), $index->getOptions());

        return $this->mongoResult('ensureIndex', $result);
    }

    /**
     * Delete indexes
     *
     * @param string $collectionName Target collection
     * @param string $index Index name to delete
     *
     * @return MongoResult
     */
    public function deleteIndex($collectionName, $index)
    {
        $collectionName = $this->collectionPrefix . $collectionName;

        $result = $this->driver->deleteIndex($collectionName, $index);

        return $this->mongoResult('deleteIndex', $result);
    }

    /**
     * Delete all collection indexes
     *
     * @param string $collectionName Target collection
     *
     * @return MongoResult
     */
    public function deleteAllIndexes($collectionName)
    {
        $result = $this->driver->deleteAllIndexes($this->collectionPrefix . $collectionName);

        return $this->mongoResult('deleteAllIndexes', $result);
    }

    /**
     * Get collection indexes
     *
     * @param string $collectionName Collection name
     *
     * @return array
     */
    public function getIndexInfo($collectionName)
    {
        $result = $this->driver->getIndexInfo($this->collectionPrefix . $collectionName);

        return $this->mongoResult('getIndexInfo', $result);
    }

    /**
     * Get collection prefix
     *
     * @return string
     */
    public function getCollectionPrefix()
    {
        return $this->collectionPrefix;
    }

    /**
     * Get database collection names
     *
     * @param bool $includeSystemCollections
     *
     * @return MongoResult
     */
    public function getCollectionNames($includeSystemCollections = false)
    {
        $result = $this->driver->getCollectionNames($includeSystemCollections);

        return $this->mongoResult('getCollectionNames', $result);
    }


    /**
     * Insert data into collection<br>
     * Inserted document <b>_id</b> is added to $data by reference
     *
     * @param string $collectionName
     * @param array  $data
     * @param array  $options options
     *
     * @return MongoResult|bool
     */
    public function insert($collectionName, array $data, $options = [])
    {
        $result = $this->driver->insert($this->collectionPrefix . $collectionName, $data, $options);
        if ($this->isArray($result)) {
            return $this->mongoResult('insert', $result);
        }

        return $result;
    }

    /**
     * Performs an operation similar to SQL's GROUP BY command
     *
     * @param string $collectionName Collection name
     * @param array  $keys Keys
     * @param array  $initial Initial
     * @param array  $reduce Reduce
     * @param array  $condition Condition
     *
     * @see http://php.net/manual/en/mongocollection.group.php
     *
     * @return MongoResult
     */
    public function group($collectionName, $keys, array $initial, $reduce, array $condition = [])
    {
        $result = $this->driver->group($this->collectionPrefix . $collectionName, $keys, $initial, $reduce, $condition);

        return $this->mongoResult('group', $result);
    }

    /**
     * Ensure index
     *
     * @param string $collectionName Name
     * @param string $keys Keys
     * @param array  $options Options
     *
     * @return MongoResult
     */
    public function ensureIndex($collectionName, $keys, $options = [])
    {
        $result = $this->driver->ensureIndex($this->collectionPrefix . $collectionName, $keys, $options);

        return $this->mongoResult('ensureIndex', $result);
    }

    /**
     * Execute JavaScript code on the database server.<br>
     * Returns result of the evaluation.
     *
     * @param string $code code
     * @param array  $args array
     *
     * @see http://php.net/manual/en/mongodb.execute.php
     *
     * @return MongoResult
     */
    public function execute($code, array $args = [])
    {
        $result = $this->driver->execute($code, $args);

        return $this->mongoResult('execute', $result);
    }

    /**
     * Find documents in given collection using given search criteria.<br>
     * If `fields` is specified, will only return the specified fields.<br>
     *
     * @param string $collectionName Collection name
     * @param array  $query Query
     * @param array  $fields Fields In form: ['fieldname' => true, 'fieldname2' => true]
     *
     * @return MongoCursor|MongoResult
     */
    public function find($collectionName, array $query = [], array $fields = [])
    {
        /* @var $result \MongoCursor */
        $result = $this->driver->find($this->collectionPrefix . $collectionName, $query, $fields);
        if ($this->isInstanceOf($result, '\MongoCursor')) {
            return new MongoCursor($result);
        }

        return $this->mongoResult('find', $result);
    }

    /**
     * Create collection
     *
     * @param string $name Name
     * @param bool   $capped Enables a capped collection. To create a capped collection, specify true. If you specify true, you must also set a maximum size in the size field.
     * @param int    $size Specifies a maximum size in bytes for a capped collection. The size field is required for capped collections. If capped is false, you can use this field to preallocate space for an ordinary collection.
     * @param int    $max The maximum number of documents allowed in the capped collection. The size limit takes precedence over this limit. If a capped collection reaches its maximum size before it reaches the maximum number of documents, MongoDB removes old documents. If you prefer to use this limit, ensure that the size limit, which is required, is sufficient to contain the documents limit.
     *
     * @return MongoResult|MongoCollection
     */
    public function createCollection($name, $capped = false, $size = 0, $max = 0)
    {
        /* @var $collection \MongoCollection */
        $result = $this->driver->createCollection($this->collectionPrefix . $name, $capped, $size, $max);
        if ($this->isInstanceOf($result, '\MongoCollection')) {
            return new MongoCollection($result);
        }

        return $this->mongoResult('createCollection', $result);
    }

    /**
     * Drop collection
     *
     * @param $collectionName
     *
     * @return MongoResult
     */
    public function dropCollection($collectionName)
    {
        $result = $this->driver->dropCollection($this->collectionPrefix . $collectionName);

        return $this->mongoResult('dropCollection', $result);
    }

    /**
     * Execute Mongo command
     *
     * @param array $data data
     *
     * @see http://php.net/manual/en/mongodb.command.php
     *
     * @return MongoResult
     */
    public function command(array $data)
    {
        $result = $this->driver->command($data);

        return $this->mongoResult('command', $result);
    }

    /**
     * Returns an array of distinct values, or FALSE on failure
     *
     * @param       $collectionName
     * @param       $key
     * @param array $query
     *
     * @return array|false
     *
     * @see      http://php.net/manual/en/mongocollection.distinct.php
     */
    public function distinct($collectionName, $key, array $query = null)
    {
        $result = $this->driver->distinct($this->collectionPrefix . $collectionName, $key, $query);
        if ($result) {
            return $this->mongoResult('distinct', $result);
        }

        return false;
    }

    /**
     * Find one<br>
     * Returns array of data or NULL if not found.
     *
     * @param string $collectionName Collection name
     * @param array  $query Query
     * @param array  $fields Fields
     *
     * @return array|null
     */
    public function findOne($collectionName, array $query = [], array $fields = [])
    {
        $result = $this->driver->findOne($this->collectionPrefix . $collectionName, $query, $fields);
        if ($result) {
            return $this->mongoResult('findOne', $result);
        }

        return null;
    }

    /**
     * Returns number of documents in given collection by given criteria.
     *
     * @param string $collectionName Collection name
     * @param array  $query Query
     *
     * @return int
     */
    public function count($collectionName, array $query = [])
    {
        return $this->driver->count($this->collectionPrefix . $collectionName, $query);
    }

    /**
     * Remove documents from collection by given criteria.<br>
     * Returns array containing result of remove operation.
     *
     * @param string $collectionName Collection name
     * @param array  $criteria Criteria
     * @param array  $options Options
     *
     * @return array
     */
    public function remove($collectionName, array $criteria, $options = [])
    {
        $result = $this->driver->remove($this->collectionPrefix . $collectionName, $criteria, $options);

        return $this->mongoResult('remove', $result);
    }

    /**
     * Insert or update existing record<br>
     * If `w` was set, returns an array containing the status of the save.<br>
     * Otherwise, returns a boolean representing if the array was not empty (an empty array will not be inserted).
     *
     * @param string $collectionName Collection name
     * @param array  $data Data
     * @param array  $options Options
     *
     * @return MongoResult|bool
     */
    public function save($collectionName, array $data, $options = [])
    {
        $result = $this->driver->save($this->collectionPrefix . $collectionName, $data, $options);
        if ($this->isArray($result)) {
            return $this->mongoResult('save', $result);
        }

        return $result;
    }

    /**
     * Update document<br>
     * Returns array containing result of update operation.
     *
     * @param string $collectionName Collection name
     * @param array  $criteria Criteria
     * @param array  $newObj New obj
     * @param array  $options Options
     *
     * @return array
     */
    public function update($collectionName, array $criteria, array $newObj, $options = [])
    {
        $result = $this->driver->update($this->collectionPrefix . $collectionName, $criteria, $newObj, $options);

        return $this->mongoResult('update', $result);
    }

    /**
     * Aggregate
     *
     * @param string $collectionName
     * @param array  $pipelines
     *
     * @return MongoResult
     */
    public function aggregate($collectionName, array $pipelines)
    {
        $args = func_get_args();
        if (count($args) > 2) {
            $pipelines = array_slice($args, 1);
        }

        $result = $this->driver->aggregate($this->collectionPrefix . $collectionName, $pipelines);

        return $this->mongoResult('aggregate', $result);
    }

    /**
     * Create MongoResult
     *
     * @param array  $data
     *
     * @param string $method
     *
     * @throws MongoException
     * @return MongoResult
     */
    private function mongoResult($method, $data)
    {
        return new $this->resultClass($method, $data);
    }
}