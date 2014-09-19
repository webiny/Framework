<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Crypt;

use Webiny\Component\ServiceManager\ServiceManager;
use Webiny\Component\ServiceManager\ServiceManagerException;

/**
 * Crypt trait.
 *
 * @package         Webiny\Component\Crypt
 */
trait CryptTrait
{

    /**
     * Get Crypt component instance.
     *
     * @param string $cryptId Name of the crypt service.
     *
     * @throws \Webiny\Component\ServiceManager\ServiceManagerException
     * @return Crypt
     */
    function crypt($cryptId)
    {
        try {
            return ServiceManager::getInstance()->getService('Crypt.' . $cryptId);
        } catch (ServiceManagerException $e) {
            throw $e;
        }
    }
}