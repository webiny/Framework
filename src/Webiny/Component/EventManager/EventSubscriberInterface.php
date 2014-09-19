<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\EventManager;

/**
 * This interface is used for event subscriber classes
 *
 * @package   Webiny\Component\EventManager
 */
interface EventSubscriberInterface
{
    /**
     * Subscribe to events
     * @return void
     */
    public function subscribe();
}