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

    const SOME_ERROR = 101;

    static protected $_messages = [
        101 => 'Some error occured.'
    ];
}