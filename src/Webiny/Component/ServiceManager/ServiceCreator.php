<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */
namespace Webiny\Component\ServiceManager;

use Webiny\Component\StdLib\StdLibTrait;

/**
 * ServiceCreator class is responsible for taking a ServiceConfig and building a service instance.
 *
 * @package         Webiny\Component\ServiceManager
 */
class ServiceCreator
{
    use StdLibTrait;

    private $config;

    /**
     * @param ServiceConfig $config Compiled service config
     */
    public function __construct(ServiceConfig $config)
    {
        $this->config = $config;
    }

    /**
     * Get service instance
     * @return object
     */
    public function getService()
    {
        // Get real arguments values
        $arguments = [];
        foreach ($this->config->getArguments() as $arg) {
            $arguments[] = $arg->value();
        }

        $service = $this->getServiceInstance($arguments);

        // Call methods
        foreach ($this->config->getCalls() as $call) {
            $arguments = [];
            foreach ($call[1] as $arg) {
                $arguments[] = $arg->value();
            }
            call_user_func_array([
                                     $service,
                                     $call[0]
                                 ], $arguments
            );
        }

        return $service;
    }

    private function getServiceInstance($arguments)
    {
        // Create service instance
        if ($this->isNull($this->config->getFactory())) {
            try {
                $reflection = new \ReflectionClass($this->config->getClass());
            } catch (\ReflectionException $e) {
                throw new ServiceManagerException(ServiceManagerException::SERVICE_CLASS_DOES_NOT_EXIST);
            }


            return $reflection->newInstanceArgs($arguments);
        }

        // Build factory instance
        $service = $this->config->getFactory()->value();
        $arguments = [];
        foreach ($this->config->getMethodArguments() as $arg) {
            $arguments[] = $arg->value();
        }

        return call_user_func_array([
                                        $service,
                                        $this->config->getMethod()
                                    ], $arguments
        );
    }
}