<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\EventManager;

/**
 * A library of EventManager functions
 *
 * @package         Webiny\Component\EventManager
 */
trait EventManagerTrait
{
    /**
     * Get event manager
     * @return EventManager
     */
    protected static function eventManager()
    {
        return EventManager::getInstance();
    }
}