<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Mongo;

/**
 * MongoCollection wraps native \MongoCollection
 *
 * @package Webiny\Component\Mongo
 */
class MongoCollection
{
    /**
     * @var \MongoCollection
     */
    private $collection;

    public function __construct(\MongoCollection $collection)
    {
        $this->collection = $collection;
    }

    public function _call($name, $arguments)
    {
        return call_user_func([
                                  $this->collection,
                                  $name
                              ], $arguments
        );
    }

    public function _get($name)
    {
        return $this->collection->$name;
    }

    public function _set($name, $value)
    {
        $this->collection->$name = $value;
    }


}