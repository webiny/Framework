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
    private $_collection;

    public function __construct(\MongoCollection $collection)
    {
        $this->_collection = $collection;
    }

    function __call($name, $arguments)
    {
        return call_user_func([
                                  $this->_collection,
                                  $name
                              ], $arguments
        );
    }

    function __get($name)
    {
        return $this->_collection->$name;
    }

    function __set($name, $value)
    {
        $this->_collection->$name = $value;
    }


}