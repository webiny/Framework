<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Entity;

/**
 * A library of Entity functions
 *
 * @package         Webiny\Component\EventManager
 */
trait EntityTrait
{
    /**
     * Get entity component
     * @return EntityPool
     */
    protected static function entity()
    {
        return EntityPool::getInstance();
    }
}