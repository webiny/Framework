<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Mongo\Driver;

use MongoClient;
use MongoDB;
use Webiny\Component\Mongo\MongoException;
use Webiny\Component\Mongo\MongoInterface;
use Webiny\Component\StdLib\StdLibTrait;


/**
 * Database
 * @package Webiny\Component\Mongo\Driver
 */
class Mongo implements MongoInterface
{
    use StdLibTrait;

    /**
     * @var MongoDB
     */
    private $db = null;

    /**
     * @var MongoClient
     */
    private $connection = null;

    public function connect($host, $database, $user = null, $password = null, array $options = [])
    {
        $config = [
            'connect' => true
        ];

        if (!$this->isNull($user) && !$this->isNull($password)) {
            $config['username'] = $user;
            $config['password'] = $password;
        }

        $config = $this->arr($config)->merge($options)->val();

        $server = 'mongodb://' . $host;
        try {
            $this->connection = new MongoClient($server, $config);
            $this->db = $this->connection->selectDB($database);
        } catch (\MongoException $e) {
            throw new MongoException($e->getMessage());
        }

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
        return $this->db->getCollectionNames($includeSystemCollections);
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
        return $this->getCollection($collectionName)->getIndexInfo();
    }

    /**
     * Insert data into collection<br>
     * Inserted document <b>_id</b> is added to $data by reference.
     *
     * @param string $collectionName
     * @param array  $data
     * @param array  $options options
     *
     * @return mixed
     */
    public function insert($collectionName, array $data, $options = [])
    {
        return $this->getCollection($collectionName)->insert($data, $options);
    }

    /**
     * Group
     *
     * @param string $collectionName collection name
     * @param array  $keys keys
     * @param array  $initial initial
     * @param array  $reduce reduce
     * @param array  $condition condition
     *
     * @return mixed
     */
    public function group($collectionName, $keys, array $initial, $reduce, array $condition = [])
    {
        $reduce = new \MongoCode($reduce);

        return $this->getCollection($collectionName)->group($keys, $initial, $reduce, $condition);
    }

    /**
     * Get reference
     *
     * @param array $ref ref
     *
     * @return \MongoDBRef
     */
    public function getReference(array $ref)
    {
        return $this->db->getDBRef($ref);
    }

    /**
     * Ensure index
     *
     * @param string $collectionName name
     * @param string $keys keys
     * @param array  $options options
     *
     * @return string|null
     */
    public function ensureIndex($collectionName, $keys, $options = [])
    {
        return $this->getCollection($collectionName)->ensureIndex($keys, $options);
    }

    /**
     * Delete index
     *
     * @param string $collectionName Collection name
     * @param array  $index Index to delete
     *
     * @see http://php.net/manual/en/mongocollection.deleteindex.php
     *
     * @return array
     */
    public function deleteIndex($collectionName, $index)
    {
        return $this->db->command([
            "deleteIndexes" => $collectionName,
            "index"         => $index,
        ]);
    }

    /**
     * Delete all indexes from given collection
     *
     * @param string $collectionName Collection name
     *
     * @return array
     */
    public function deleteAllIndexes($collectionName)
    {
        return $this->getCollection($collectionName)->deleteIndexes();
    }

    /**
     * Execute
     *
     * @param string $code code
     * @param array  $args array
     *
     * @return string|null
     */
    public function execute($code, array $args = [])
    {
        return $this->db->execute($code, $args);
    }

    /**
     * Find
     *
     * @param string $collectionName collection name
     * @param array  $query query
     * @param array  $fields fields
     *
     * @see http://php.net/manual/en/mongocollection.find.php
     *
     * @return mixed
     */
    public function find($collectionName, array $query = [], array $fields = [])
    {
        return $this->getCollection($collectionName)->find($query, $fields);
    }

    /**
     * Create collection
     *
     * @param string $name name
     * @param bool   $capped Enables a capped collection. To create a capped collection, specify true. If you specify true, you must also set a maximum size in the size field.
     * @param int    $size Specifies a maximum size in bytes for a capped collection. The size field is required for capped collections. If capped is false, you can use this field to preallocate space for an ordinary collection.
     * @param int    $max The maximum number of documents allowed in the capped collection. The size limit takes precedence over this limit. If a capped collection reaches its maximum size before it reaches the maximum number of documents, MongoDB removes old documents. If you prefer to use this limit, ensure that the size limit, which is required, is sufficient to contain the documents limit.
     *
     * @return string|null
     */
    public function createCollection($name, $capped = false, $size = 0, $max = 0)
    {
        return $this->db->createCollection($name, $capped, $size, $max);
    }

    /**
     * Drop collection
     *
     * @param $collectionName
     *
     * @return string|null
     */
    public function dropCollection($collectionName)
    {
        return $this->db->dropCollection($collectionName);
    }

    /**
     * Command
     *
     * @param array $data data
     *
     * @return string|null
     */
    public function command(array $data)
    {
        return $this->db->command($data);
    }

    /**
     * Distinct
     *
     * @param       $collectionName
     * @param array $key
     * @param array $query
     *
     * @return null|string
     * @internal param array $data data
     *
     */
    public function distinct($collectionName, $key, array $query = null)
    {
        return $this->getCollection($collectionName)->distinct($key, $query);
    }

    /**
     * Find one
     *
     * @param string $collectionName collection name
     * @param array  $query query
     * @param array  $fields fields
     *
     * @return mixed
     */
    public function findOne($collectionName, array $query = [], array $fields = [])
    {
        return $this->getCollection($collectionName)->findOne($query, $fields);
    }

    /**
     * Count
     *
     * @param string $collectionName collection name
     * @param array  $query query
     *
     * @return mixed
     */
    public function count($collectionName, array $query = [])
    {
        return $this->getCollection($collectionName)->count($query);
    }

    /**
     * Remove
     *
     * @param string $collectionName collection name
     * @param array  $criteria criteria
     * @param array  $options options
     *
     * @see http://php.net/manual/en/mongocollection.remove.php
     *
     * @return mixed
     */
    public function remove($collectionName, array $criteria, $options = [])
    {
        return $this->getCollection($collectionName)->remove($criteria, $options);
    }

    /**
     * Save
     *
     * @param string $collectionName collection name
     * @param array  $data data
     * @param array  $options options
     *
     * @see http://php.net/manual/en/mongocollection.save.php
     *
     * @return mixed
     */
    public function save($collectionName, array $data, $options = [])
    {
        return $this->getCollection($collectionName)->save($data, $options);
    }

    /**
     * Update
     *
     * @param string $collectionName collection name
     * @param array  $criteria criteria
     * @param array  $newObj new obj
     * @param array  $options options
     *
     * @see http://php.net/manual/en/mongocollection.update.php
     *
     * @return mixed
     */
    public function update($collectionName, array $criteria, array $newObj, $options = [])
    {
        return $this->getCollection($collectionName)->update($criteria, $newObj, $options);
    }

    /**
     * Aggregate
     *
     * @param $collectionName
     * @param $pipelines
     *
     * @return array
     */
    public function aggregate($collectionName, array $pipelines)
    {
        return $this->getCollection($collectionName)->aggregate($pipelines);
    }

    /**
     * @param $collection
     *
     * @return \MongoCollection
     */
    private function getCollection($collection)
    {
        return $this->connection->selectCollection($this->db, $collection);
    }
}