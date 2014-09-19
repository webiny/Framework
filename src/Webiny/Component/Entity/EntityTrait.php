<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @link      http://www.webiny.com/wf-snv for the canonical source repository
 * @copyright Copyright @ 2009-2013 Webiny LTD. (http://www.webiny.com)
 * @license   http://www.webiny.com/framework/license
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
     * Get entity pool
     * @return EntityPool
     */
    protected static function entity()
    {
        return EntityPool::getInstance();
    }
}