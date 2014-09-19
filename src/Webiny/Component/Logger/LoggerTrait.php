<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Logger;

use Webiny\Component\Logger\Bridge\LoggerDriverInterface;
use Webiny\Component\Logger\Driver\NullDriver;
use Webiny\Component\ServiceManager\ServiceManager;
use Webiny\Component\ServiceManager\ServiceManagerException;

/**
 * Logger trait.
 *
 * @package        Webiny\Component\Logger
 */
trait LoggerTrait
{

    /**
     * Get logger.
     * Just provide the logger name without the 'logger.' prefix.
     * The name must match the name of your service.
     *
     * @param string $name Logger service name.
     *
     * @return LoggerDriverInterface
     * @throws ServiceManagerException
     */
    public static function logger($name = 'Webiny')
    {
        try {
            return ServiceManager::getInstance()->getService('Logger.' . $name);
        } catch (ServiceManagerException $e) {
            if ($e->getCode() == ServiceManagerException::SERVICE_DEFINITION_NOT_FOUND) {
                return new Logger($name, new NullDriver());
            }

            throw $e;
        }

    }

}