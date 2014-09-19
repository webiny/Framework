<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Mongo;

use Webiny\Component\StdLib\ComponentTrait;
use Webiny\Component\StdLib\StdLibTrait;

/**
 * Mongo
 *
 * @TODO    handle \MongoException properly and wrap it into Webiny MongoException
 *
 * @package Webiny\Component\Mongo
 */
class Mongo
{
    use ComponentTrait, StdLibTrait;

    /**
     * @var MongoInterface
     */
    private $_driver = null;

    private $_collectionPrefix = '';

    private static $_defaultConfig = [
        'Database' => 'Webiny',
        'Services' => [
            'Webiny' => [
                'Class'     => '\Webiny\Component\Mongo\Mongo',
                'Arguments' => [
                    '127.0.0.1:27017',
                    'webiny',
                    null,
                    null,
                    ''
                ]
            ]
        ],
        'Driver'   => '\Webiny\Component\Mongo\Driver\Webiny'
    ];

    public function __construct($host, $database, $user = null, $password = null, $collectionPrefix = '', $options = [])
    {
        $mongoBridge = $this->getConfig()->get('Driver', '\Webiny\Component\Mongo\Driver\Mongo');
        $this->_driver = new $mongoBridge();
        $this->_driver->connect($host, $database, $user, $password, $options);
        $this->_collectionPrefix = $collectionPrefix;
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
     * Get collection prefix
     * @return string
     */
    public function getCollectionPrefix()
    {
        return $this->_collectionPrefix;
    }

    /**
     * Get database collection names
     *
     * @param bool $includeSystemCollections
     *
     * @return array
     */
    public function getCollectionNames($includeSystemCollections = false)
    {
        return $this->_driver->getCollectionNames($includeSystemCollections);
    }


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
    public function insert($collectionName, array $data, $options = [])
    {
        return $this->_driver->insert($this->_collectionPrefix . $collectionName, $data, $options);
    }

    /**
     * Performs an operation similar to SQL's GROUP BY command
     *
     * @param string $collectionName Collection name
     * @param array  $keys           Keys
     * @param array  $initial        Initial
     * @param array  $reduce         Reduce
     * @param array  $condition      Condition
     *
     * @see http://php.net/manual/en/mongocollection.group.php
     *
     * @return array
     */
    public function group($collectionName, $keys, array $initial, $reduce, array $condition = [])
    {
        return $this->_driver->group($this->_collectionPrefix . $collectionName, $keys, $initial, $reduce, $condition);
    }

    /**
     * Ensure index<br>
     * Returns an array containing the status of the index creation.
     * <code>
     * Array
     *   (
     *       [n] => 0
     *       [connectionId] => 60
     *       [err] =>
     *       [ok] => 1
     *   )
     * </code>
     *
     * @param string $collectionName Name
     * @param string $keys           Keys
     * @param array  $options        Options
     *
     * @return array
     */
    public function ensureIndex($collectionName, $keys, $options = [])
    {
        return $this->_driver->ensureIndex($this->_collectionPrefix . $collectionName, $keys, $options);
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
     * @return mixed
     */
    public function execute($code, array $args = [])
    {
        return $this->_driver->execute($code, $args);
    }

    /**
     * Find documents in given collection using given search criteria.<br>
     * If `fields` is specified, will only return the specified fields.<br>
     *
     * @param string $collectionName Collection name
     * @param array  $query          Query
     * @param array  $fields         Fields In form: ['fieldname' => true, 'fieldname2' => true]
     *
     * @return MongoCursor
     */
    public function find($collectionName, array $query = [], array $fields = [])
    {
        /* @var $result \MongoCursor */
        $result = $this->_driver->find($this->_collectionPrefix . $collectionName, $query, $fields);
        if ($this->isInstanceOf($result, '\MongoCursor')) {
            return new MongoCursor($result);
        }

        return $result;
    }

    /**
     * Create collection
     *
     * @param string $name   Name
     * @param bool   $capped Enables a capped collection. To create a capped collection, specify true. If you specify true, you must also set a maximum size in the size field.
     * @param int    $size   Specifies a maximum size in bytes for a capped collection. The size field is required for capped collections. If capped is false, you can use this field to preallocate space for an ordinary collection.
     * @param int    $max    The maximum number of documents allowed in the capped collection. The size limit takes precedence over this limit. If a capped collection reaches its maximum size before it reaches the maximum number of documents, MongoDB removes old documents. If you prefer to use this limit, ensure that the size limit, which is required, is sufficient to contain the documents limit.
     *
     * @return array
     */
    public function createCollection($name, $capped = false, $size = 0, $max = 0)
    {
        $collection = $this->_driver->createCollection($this->_collectionPrefix . $name, $capped, $size, $max);
        if ($this->isInstanceOf($collection, '\MongoCollection')) {
            return new MongoCollection($collection);
        }

        return $collection;
    }

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
    public function dropCollection($collectionName)
    {
        return $this->_driver->dropCollection($this->_collectionPrefix . $collectionName);
    }

    /**
     * Execute Mongo command
     *
     * @param array $data data
     *
     * @see http://php.net/manual/en/mongodb.command.php
     *
     * @return string|null
     */
    public function command(array $data)
    {
        return $this->_driver->command($data);
    }

    /**
     * Returns an array of distinct values, or FALSE on failure
     *
     * @param array $data Aggregation data
     *
     * @see http://php.net/manual/en/mongocollection.distinct.php
     *
     * @return array|false
     */
    public function distinct(array $data)
    {
        return $this->_driver->distinct($data);
    }

    /**
     * Find one<br>
     * Returns array of data or NULL if not found.
     *
     * @param string $collectionName Collection name
     * @param array  $query          Query
     * @param array  $fields         Fields
     *
     * @return array|null
     */
    public function findOne($collectionName, array $query = [], array $fields = [])
    {
        return $this->_driver->findOne($this->_collectionPrefix . $collectionName, $query, $fields);
    }

    /**
     * Returns number of documents in given collection by given criteria.
     *
     * @param string $collectionName Collection name
     * @param array  $query          Query
     *
     * @return int
     */
    public function count($collectionName, array $query = [])
    {
        return $this->_driver->count($this->_collectionPrefix . $collectionName, $query);
    }

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
     * @param string $collectionName Collection name
     * @param array  $criteria       Criteria
     * @param array  $options        Options
     *
     * @return array
     */
    public function remove($collectionName, array $criteria, $options = [])
    {
        return $this->_driver->remove($this->_collectionPrefix . $collectionName, $criteria, $options);
    }

    /**
     * Insert or update existing record<br>
     * If `w` was set, returns an array containing the status of the save.<br>
     * Otherwise, returns a boolean representing if the array was not empty (an empty array will not be inserted).
     *
     * @param string $collectionName Collection name
     * @param array  $data           Data
     * @param array  $options        Options
     *
     * @return array|bool
     */
    public function save($collectionName, array $data, $options = [])
    {
        return $this->_driver->save($this->_collectionPrefix . $collectionName, $data, $options);
    }

    /**
     * Aggregate documents<br>
     * Returns the result of the aggregation as an array.<br>
     * The ok will be set to 1 on success, 0 on failure.
     *
     * @param array $options
     *
     * @see http://php.net/manual/en/mongocollection.aggregate.php
     *
     * @return array
     */
    public function aggregate(array $options)
    {
        return $this->_driver->aggregate($options);
    }

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
     * @param string $collectionName Collection name
     * @param array  $criteria       Criteria
     * @param array  $newObj         New obj
     * @param array  $options        Options
     *
     * @return array
     */
    public function update($collectionName, array $criteria, array $newObj, $options = [])
    {
        return $this->_driver->update($this->_collectionPrefix . $collectionName, $criteria, $newObj, $options);
    }
}