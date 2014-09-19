<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @link      http://www.webiny.com/wf-snv for the canonical source repository
 * @copyright Copyright @ 2009-2013 Webiny LTD. (http://www.webiny.com)
 * @license   http://www.webiny.com/framework/license
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