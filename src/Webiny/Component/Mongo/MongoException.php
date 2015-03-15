<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */
namespace Webiny\Component\Mongo;

use Webiny\Component\StdLib\Exception\ExceptionAbstract;

/**
 * Exception class for the Mongo component.
 *
 * @package         Webiny\Component\Mongo
 */
class MongoException extends ExceptionAbstract
{

    const SINGLE_INDEX_TOO_MANY_FIELDS = 101;
    const COMPOUND_INDEX_NOT_ENOUGH_FIELDS = 102;
    const INVALID_RESULT_CLASS_PROVIDED = 103;

    protected static $messages = [
        101 => 'SingleIndex can only accept one index field. %s fields given.',
        102 => 'CompoundIndex must contain at least 2 fields.',
        103 => 'Result class must be an instance or subclass of `\Webiny\Component\Mongo\MongoResult`.'
    ];
}