<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */
namespace Webiny\Component\Mongo;

use Webiny\Component\StdLib\Exception\AbstractException;

/**
 * Exception class for the Mongo component.
 *
 * @package         Webiny\Component\Mongo
 */
class MongoException extends AbstractException
{

    const SINGLE_INDEX_TOO_MANY_FIELDS = 101;
    const COMPOUND_INDEX_NOT_ENOUGH_FIELDS = 102;
    const INVALID_RESULT_CLASS_PROVIDED = 103;
    const SPHERE_INDEX_TOO_MANY_FIELDS = 104;

    protected static $messages = [
        101 => 'SingleIndex can only accept one index field. %s fields given.',
        102 => 'CompoundIndex must contain at least 2 fields.',
        103 => 'Result class must be an instance or subclass of `\Webiny\Component\Mongo\MongoResult`.',
        104 => 'SphereIndex can only accept one index field. %s fields given.'
    ];
}