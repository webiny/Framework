<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Mongo;

use Webiny\Component\ServiceManager\ServiceManager;

/**
 * Trait for Mongo component.
 *
 * @package         Webiny\Component\Mongo
 */
trait MongoTrait
{
    /**
     * @param string $database Mongo service name (Default: Webiny)
     *
     * @return Mongo
     */
    protected static function mongo($database = 'Webiny')
    {
        return ServiceManager::getInstance()->getService('Mongo.' . $database);
    }
}