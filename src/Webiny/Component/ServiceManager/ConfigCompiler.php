<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\ServiceManager;

use Webiny\Component\Config\ConfigObject;
use Webiny\Component\StdLib\StdLibTrait;
use Webiny\Component\StdLib\StdObject\ArrayObject\ArrayObject;


/**
 * ConfigCompiler class is responsible for compiling a valid ServiceConfig object.<br />
 * It parses the config file, manages inheritance, wraps arguments into Argument objects
 * and makes sure the config is valid.
 *
 * @package         Webiny\Component\ServiceManager
 */
class ConfigCompiler
{
    use StdLibTrait;

    private $serviceName;
    private $serviceConfig;
    private $parameters;

    /**
     * @param string       $serviceName Service name
     * @param ConfigObject $config      ConfigObject to compile
     * @param array        $parameters  Parameters to use when parsing $config
     */
    public function __construct($serviceName, ConfigObject $config, $parameters)
    {
        $this->serviceName = $serviceName;
        $this->serviceConfig = $config->toArray(true);
        $this->parameters = $parameters;
    }

    /**
     * Compile current config and return a valid ServiceConfig object.
     * That new ServiceConfig will be used to instantiate a service later in the process of creating a service instance.
     *
     * @return ServiceConfig
     */
    public function compile()
    {
        $this->manageInheritance();
        $this->serviceConfig = $this->insertParameters($this->serviceConfig);
        $this->buildArguments('Arguments');
        $this->buildArguments('MethodArguments');
        $this->buildCallsArguments();
        $this->buildFactoryArgument();

        return $this->buildServiceConfig();
    }

    /**
     * Check if current service has a parent service and merge its config with parent service config.
     *
     * @throws ServiceManagerException
     */
    private function manageInheritance()
    {
        $config = $this->serviceConfig;
        if ($config->keyExists('Parent')) {
            $parentServiceName = $this->str($config->key('Parent'))->trimLeft('@')->val();
            $parentConfig = ServiceManager::getInstance()->getServiceConfig($parentServiceName)->toArray(true);
            if (!$parentConfig->keyExists('Abstract')) {
                throw new ServiceManagerException(ServiceManagerException::SERVICE_IS_NOT_ABSTRACT,
                                                  [$config->key('Parent')]
                );
            }
            $config = $this->extendConfig($config, $parentConfig);
        }

        // Check if it's a potentially valid service definition
        if (!$config->keyExists('Class') && !$config->keyExists('Factory')) {
            throw new ServiceManagerException(ServiceManagerException::SERVICE_CLASS_KEY_NOT_FOUND,
                                              [$this->serviceName]
            );
        }

        $this->serviceConfig = $config;
    }

    /**
     * Insert parameters into the config
     *
     * @param ArrayObject $config Target config
     *
     * @return ArrayObject
     *
     * @throws ServiceManagerException
     */
    private function insertParameters($config)
    {
        foreach ($config as $k => $v) {
            if ($this->isArray($v)) {
                $config[$k] = $this->insertParameters($v);
            } elseif ($this->isString($v)) {
                $str = $this->str($v)->trim();
                if ($str->startsWith('%') && $str->endsWith('%')) {
                    $parameter = $str->trim('%')->val();
                    if (isset($this->parameters[$parameter])) {
                        $config[$k] = $this->parameters[$parameter];
                    } else {
                        throw new ServiceManagerException(ServiceManagerException::PARAMETER_NOT_FOUND, [
                                $parameter,
                                $this->serviceName
                            ]
                        );
                    }

                }
            }
        }

        return $config;
    }

    /**
     * Extend $config with $parentConfig
     *
     * @param ArrayObject $config       Child config object
     * @param ArrayObject $parentConfig Parent config object
     *
     * @return ArrayObject
     */
    private function extendConfig(ArrayObject $config, ArrayObject $parentConfig)
    {

        $configCalls = null;
        $overrideCalls = false;

        // Get calls arrays
        if ($config->keyExists('Calls')) {
            $configCalls = $config->key('Calls');
        } elseif ($config->keyExists('!Calls')) {
            $configCalls = $config->key('!Calls');
            $overrideCalls = true;
        }
        $parentCalls = $parentConfig->key('Calls', [], true);

        // Merge basic values
        $config = $parentConfig->merge($config);

        // Remove unnecessary keys
        $config->removeKey('Parent')->removeKey('Abstract')->removeKey('Calls');

        // Merge calls
        if (!$this->isNull($configCalls) && !$this->isNull($parentCalls)) {
            if ($overrideCalls) {
                $config->key('!Calls', $configCalls);

                return;
            }

            foreach ($configCalls as $call) {
                $call = $this->arr($call);
                if ($call->keyExists(2)) {
                    $parentCalls[$call[2]] = $call->val();
                } else {
                    $parentCalls[] = $call->val();
                }
            }
            $config->key('Calls', $parentCalls);
        } elseif ($this->isNull($configCalls) && !$this->isNull($parentCalls)) {
            $config->key('Calls', $parentCalls);
        } elseif (!$this->isNull($configCalls) && $this->isNull($parentCalls)) {
            $config->key('Calls', $configCalls);
        }

        return $config;
    }

    /**
     * Convert simple config arguments into Argument objects
     *
     * @param string $key
     *
     * @throws ServiceManagerException
     */
    private function buildArguments($key)
    {
        $newArguments = [];
        if ($this->serviceConfig->keyExists($key)) {
            $arguments = $this->serviceConfig->key($key);
            if (!$this->isArray($arguments)) {
                throw new ServiceManagerException(ServiceManagerException::INVALID_SERVICE_ARGUMENTS_TYPE,
                                                  [$this->serviceName]
                );
            }
            foreach ($arguments as $arg) {
                $newArguments[] = new Argument($arg);
            }
        }
        $this->serviceConfig->key($key, $newArguments);
    }

    /**
     * Convert factory service arguments into FactoryArgument objects
     */
    private function buildFactoryArgument()
    {
        if ($this->serviceConfig->keyExists('Factory')) {
            $factory = $this->str($this->serviceConfig->key('Factory'));
            $arguments = $this->serviceConfig->key('Arguments', null, true);
            // If it's a STATIC method call - unset all arguments
            if ($this->serviceConfig->key('Static', true, true) && !$factory->startsWith('@')) {
                $arguments = [];
            }
            $factoryArgument = new FactoryArgument($this->serviceConfig->key('Factory'), $arguments,
                                                   $this->serviceConfig->key('Static')
            );
            $this->serviceConfig->key('Factory', $factoryArgument);
        }
    }

    /**
     * Build arguments for "Calls" methods
     */
    private function buildCallsArguments()
    {
        if ($this->serviceConfig->keyExists('Calls')) {
            $calls = $this->serviceConfig->key('Calls');
            foreach ($calls as $callKey => $call) {
                if ($this->isArray($call[1])) {
                    $newArguments = [];
                    foreach ($call[1] as $arg) {
                        $newArguments[] = new Argument($arg);
                    }
                    $calls[$callKey][1] = $newArguments;
                }
            }
            $this->serviceConfig->key('Calls', $calls);
        }
    }

    /**
     * Build final ServiceConfig object
     *
     * @return ServiceConfig
     *
     * @throws ServiceManagerException
     */
    private function buildServiceConfig()
    {
        if ($this->serviceConfig->keyExists('Factory') && !$this->serviceConfig->keyExists('Method')) {
            throw new ServiceManagerException(ServiceManagerException::FACTORY_SERVICE_METHOD_KEY_MISSING,
                                              [$this->serviceName]
            );
        }

        $config = new ServiceConfig();
        $config->setClass($this->serviceConfig->key('Class', null, true));
        $config->setArguments($this->serviceConfig->key('Arguments', [], true));
        $config->setCalls($this->serviceConfig->key('Calls', [], true));
        $config->setScope($this->serviceConfig->key('Scope', ServiceScope::CONTAINER, true));
        $config->setFactory($this->serviceConfig->key('Factory', null, true));
        $config->setMethod($this->serviceConfig->key('Method', null, true));
        $config->setMethodArguments($this->serviceConfig->key('MethodArguments'));
        $config->setStatic($this->serviceConfig->key('Static', true, true));

        return $config;
    }
}