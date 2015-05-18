<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Mongo;

use MongoCursorException;

/**
 * MongoCursor wraps native \MongoCursor
 *
 * @package Webiny\Component\Mongo
 */
class MongoCursor implements \Iterator
{
    /**
     * @var \MongoCursor
     */
    private $cursor;

    public function __construct(\MongoCursor $cursor)
    {
        $this->cursor = $cursor;
    }

    public function __call($name, $arguments)
    {
        if (count($arguments)) {
            return call_user_func([
                $this->cursor,
                $name
            ], $arguments);
        }

        return $this->cursor->$name();
    }

    /**
     * Limits the number of results returned
     * @link http://www.php.net/manual/en/mongocursor.limit.php
     * @param int $num The number of results to return.
     * @throws MongoCursorException
     * @return $this Returns this cursor
     */
    public function limit($num) {
        $this->cursor->limit($num);
        return $this;
    }

    /**
     * Skips a number of results
     * @link http://www.php.net/manual/en/mongocursor.skip.php
     * @param int $num The number of results to skip.
     * @throws MongoCursorException
     * @return $this Returns this cursor
     */
    public function skip($num) {
        $this->cursor->skip($num);
        return $this;
    }

    /**
     * Sorts the results by given fields
     * @link http://www.php.net/manual/en/mongocursor.sort.php
     * @param array $fields An array of fields by which to sort. Each element in the array has as key the field name, and as value either 1 for ascending sort, or -1 for descending sort
     * @throws MongoCursorException
     * @return $this Returns the same cursor that this method was called on
     */
    public function sort(array $fields) {
        $this->cursor->sort($fields);
        return $this;
    }

    /**
     * Clears the cursor
     * @link http://www.php.net/manual/en/mongocursor.reset.php
     * @return $this
     */
    public function reset() {
        $this->cursor->reset();
        return $this;
    }

    /**
     * Gets the query, fields, limit, and skip for this cursor
     * @link http://www.php.net/manual/en/mongocursor.info.php
     * @return array The query, fields, limit, and skip for this cursor as an associative array.
     */
    public function info(){
        return $this->cursor->info();
    }

    /**
     * Counts the number of results for this query
     * @link http://www.php.net/manual/en/mongocursor.count.php
     * @param bool $all Count records with limit and skip applied
     * @return int The number of documents returned by this cursor's query.
     */
    public function count($all = false) {
        return $this->cursor->count($all);
    }

    /**
     * Sets the fields for a query
     * @link http://www.php.net/manual/en/mongocursor.fields.php
     * @param array $fields Fields to return (or not return).
     * @throws MongoCursorException
     * @return MongoCursor
     */
    public function fields(array $fields){
        $this->cursor->fields($fields);
        return $this;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function current()
    {
        return $this->cursor->current();
    }

    /**
     * Return an explanation of the query, often useful for optimization and debugging
     * @link http://www.php.net/manual/en/mongocursor.explain.php
     * @return array Returns an explanation of the query.
     */
    public function explain() {
        return $this->cursor->explain();
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        $this->cursor->next();
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        return $this->cursor->key();
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid()
    {
        return $this->cursor->valid();
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        $this->cursor->rewind();
    }

    /**
     * Get cursor data
     *
     * @return array
     */
    public function getData(){
        $data = [];
        foreach($this->cursor as $result){
            $data[] = $result;
        }
        return $data;
    }
}