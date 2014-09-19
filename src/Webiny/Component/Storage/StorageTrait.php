<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @link      http://www.webiny.com/wf-snv for the canonical source repository
 * @copyright Copyright @ 2009-2013 Webiny LTD. (http://www.webiny.com)
 * @license   http://www.webiny.com/framework/license
 */

namespace Webiny\Component\Storage;

use Webiny\Component\ServiceManager\ServiceManager;
use Webiny\Component\ServiceManager\ServiceManagerException;

/**
 * A library of Storage functions
 *
 * @package Webiny\Component\Storage
 */
trait StorageTrait
{
    /**
     * Get storage
     *
     * @param string $storageName Storage name
     *
     * @throws \Webiny\Component\ServiceManager\ServiceManagerException
     * @return Storage
     */
    protected static function storage($storageName)
    {
        try {
            return ServiceManager::getInstance()->getService('Storage.' . $storageName);
        } catch (ServiceManagerException $e) {
            throw $e;
        }
    }
}