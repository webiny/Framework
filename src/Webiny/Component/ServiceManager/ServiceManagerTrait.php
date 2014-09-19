<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @link      http://www.webiny.com/wf-snv for the canonical source repository
 * @copyright Copyright @ 2009-2013 Webiny LTD. (http://www.webiny.com)
 * @license   http://www.webiny.com/framework/license
 */

namespace Webiny\Component\ServiceManager;

/**
 * A library of ServiceManager functions
 *
 * @package         Webiny\Component\ServiceManager
 */
trait ServiceManagerTrait
{
    /**
     * Get service
     *
     * @param string $serviceName Service name
     *
     * @return object
     */
    protected static function service($serviceName)
    {
        return ServiceManager::getInstance()->getService($serviceName);
    }

    /**
     * Get multiple services by tag
     *
     * @param string $tag       Tag to use for services filter
     * @param null   $forceType (Optional) Return only services which are instances of $forceType
     *
     * @return array
     */
    protected static function servicesByTag($tag, $forceType = null)
    {
        return ServiceManager::getInstance()->getServicesByTag($tag, $forceType);
    }
}