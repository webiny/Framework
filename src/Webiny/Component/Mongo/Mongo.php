<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @link      http://www.webiny.com/wf-snv for the canonical source repository
 * @copyright Copyright (c) 2009-2013 Webiny LTD. (http://www.webiny.com)
 * @license   http://www.webiny.com/framework/license
 */

namespace Webiny\Component\Mongo;

use MongoDB\BSON\ObjectID;
use MongoDB\Model\BSONDocument;
use Webiny\Component\Mongo\Bridge\MongoInterface;
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
    private $bridge = null;

    /**
     * @var string
     */
    private $collectionPrefix = '';

    public function __construct($uri, array $uriOptions = [], array $driverOptions = [], $collectionPrefix = '')
    {
        $mongoBridge = $this->getConfig()->get('Driver', '\Webiny\Component\Mongo\Bridge\MongoDb');
        $this->bridge = new $mongoBridge();
        $this->bridge->connect($uri, $uriOptions, $driverOptions);
        $this->collectionPrefix = $collectionPrefix;
    }

    /**
     * Select database
     *
     * @param string $database
     */
    public function selectDatabase($database)
    {
       $this->bridge->selectDatabase($database);
    }

    /**
     * Construct Mongo ID
     *
     * @param null|string $id (Optional)
     *
     * @return ObjectID
     */
    public function id($id = null)
    {
        return $this->bridge->id($id);
    }

    /**
     * Check if given string is a valid MongoId string<br>
     *
     * @param $id
     *
     * @return bool
     */
    public function isId($id)
    {
        return $this->bridge->isId($id);
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
     * List collections
     *
     * @param array $options
     *
     * @return mixed
     */
    public function listCollections(array $options = [])
    {
        return iterator_to_array($this->bridge->listCollections($options));
    }

    /**
     * Create collection
     *
     * @param string $name Name
     * @param array  $options
     *
     * @return array|object
     */
    public function createCollection($name, array $options = [])
    {
        return $this->bridge->createCollection($this->cName($name), $options);
    }

    /**
     * Drop collection
     *
     * @param string $collectionName
     * @param array  $options
     *
     * @return array|object
     */
    public function dropCollection($collectionName, array $options = [])
    {
        return $this->bridge->dropCollection($this->cName($collectionName), $options);
    }

    public function aggregate($collectionName, array $pipeline, array $options = [])
    {
        return $this->bridge->aggregate($this->cName($collectionName), $pipeline, $options);
    }

    public function bulkWrite($collectionName, array $operations, array $options = [])
    {
        return $this->bridge->bulkWrite($this->cName($collectionName), $operations, $options);
    }

    public function count($collectionName, $filter = [], array $options = [])
    {
        return $this->bridge->count($this->cName($collectionName), $filter, $options);
    }

    public function createIndex($collectionName, IndexInterface $index, array $options = [])
    {
        return $this->bridge->createIndex($this->cName($collectionName), $index->getFields(), $index->getOptions() + $options);
    }

    public function createIndexes($collectionName, array $indexes)
    {
        $mongoIndexes = [];
        /* @var $index IndexInterface */
        foreach ($indexes as $index) {
            $mongoIndexes[] = ['key' => $index->getFields()] + $index->getOptions();
        }

        return $this->bridge->createIndexes($this->cName($collectionName), $mongoIndexes);
    }

    public function delete($collectionName, $filter, array $options = [])
    {
        return $this->bridge->delete($this->cName($collectionName), $filter, $options);
    }

    public function distinct($collectionName, $fieldName, $filter = [], array $options = [])
    {
        return $this->bridge->distinct($this->cName($collectionName), $fieldName, $filter, $options);
    }

    public function dropIndex($collectionName, $indexName, array $options = [])
    {
        return $this->bridge->dropIndex($this->cName($collectionName), $indexName, $options);
    }

    public function dropIndexes($collectionName, array $options = [])
    {
        return $this->bridge->dropIndexes($this->cName($collectionName), $options);
    }

    public function find($collectionName, $filter = [], $sort = [], $limit = 0, $skip = 0, array $options = [])
    {
        $options += [
            'limit' => $limit,
            'skip'  => $skip,
            'sort'  => $sort
        ];

        $result = $this->bridge->find($this->cName($collectionName), $filter, $options)->toArray();
        $data = [];
        /* @var $r BSONDocument */
        foreach ($result as $r) {
            $data[] = iterator_to_array($r->getIterator());
        }

        return $data;
    }

    public function findOne($collectionName, $filter = [], array $options = [])
    {
        return $this->bridge->findOne($this->cName($collectionName), $filter, $options);
    }

    public function findOneAndDelete($collectionName, $filter, array $options = [])
    {
        return $this->bridge->findOneAndDelete($this->cName($collectionName), $filter, $options);
    }

    public function findOneAndReplace($collectionName, $filter, $replacement, array $options = [])
    {
        return $this->bridge->findOneAndReplace($this->cName($collectionName), $filter, $replacement, $options);
    }

    public function findOneAndUpdate($collectionName, $filter, $update, array $options = [])
    {
        return $this->bridge->findOneAndUpdate($this->cName($collectionName), $filter, $update, $options);
    }

    public function getNamespace($collectionName)
    {
        return $this->bridge->getNamespace($this->cName($collectionName));
    }

    public function insertMany($collectionName, array $documents, array $options = [])
    {
        return $this->bridge->insertMany($this->cName($collectionName), $documents, $options);
    }

    public function insertOne($collectionName, $document, array $options = [])
    {
        return $this->bridge->insertOne($this->cName($collectionName), $document, $options);
    }

    /**
     * @param string $collectionName
     * @param array  $options
     *
     * @return array
     */
    public function listIndexes($collectionName, array $options = [])
    {
        return iterator_to_array($this->bridge->listIndexes($this->cName($collectionName), $options));
    }

    public function update($collectionName, $filter, $update, array $options = [])
    {
        return $this->bridge->update($this->cName($collectionName), $filter, $update, $options);
    }

    public function command($command, array $options = [])
    {
        return $this->bridge->command($command, $options);
    }

    /**
     * Get collection name
     *
     * @param $collectionName
     *
     * @return string
     */
    private function cName($collectionName)
    {
        return $this->collectionPrefix . $collectionName;
    }
}