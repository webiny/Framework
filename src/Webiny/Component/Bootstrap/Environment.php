<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Bootstrap;

use Webiny\Component\ClassLoader\ClassLoader;
use Webiny\Component\Config\ConfigObject;
use Webiny\Component\Config\ConfigTrait;
use Webiny\Component\Http\HttpTrait;
use Webiny\Component\StdLib\SingletonTrait;
use Webiny\Component\StdLib\StdObjectTrait;

/**
 * Bootstrap environment class.
 * Detects current environment and loads the appropriate configurations.
 *
 * @package         Webiny\Component\Bootstrap
 */
class Environment
{
    use SingletonTrait, ConfigTrait, HttpTrait, StdObjectTrait;

    /**
     * @var ConfigObject Application configuration (App.yaml from users application).
     */
    private $_applicationConfig;

    /**
     * @var string Absolute path to the root of users application.
     */
    private $_applicationAbsolutePath;

    /**
     * @var string Name of the current environment.
     */
    private $_currentEnvironmentName;

    /**
     * @var ConfigObject List of loaded configurations from the current environment.
     */
    private $_componentConfigs;

    /**
     * @var array List of Webiny Framework components, defined in order that we don't have problems with dependencies
     * between the components.
     */
    private $_webinyComponents = [
        'ClassLoader',
        'Config',
        'ServiceManager',
        'Cache',
        'Amazon',
        'Annotations',
        'Crypt',
        'EventManager',
        'Http',
        'Logger',
        'Mailer',
        'Mongo',
        'Entity',
        'OAuth2',
        'Router',
        'Storage',
        'TwitterOAuth',
        'Image',
        'Security',
        'TemplateEngine',
        'Rest'
    ];


    /**
     * Initializes the environment and loads all the configurations from it.
     *
     * @param string $applicationAbsolutePath Absolute path to the root of the application.
     *
     * @throws BootstrapException
     */
    public function initializeEnvironment($applicationAbsolutePath)
    {
        $this->_applicationAbsolutePath = $applicationAbsolutePath;

        $this->_loadApplicationConfig();
        $this->_registerAppNamespace();
        $this->_initializeEnvironment();
        $this->_initializeComponentConfigurations();
        $this->_setErrorReporting();
    }

    /**
     * Get current application configuration.
     *
     * @return ConfigObject
     */
    public function getApplicationConfig()
    {
        return $this->_applicationConfig->Application;
    }

    /**
     * Get the current application absolute path.
     *
     * @return string
     */
    public function getApplicationAbsolutePath()
    {
        return $this->_applicationAbsolutePath;
    }

    /**
     * Get all loaded configurations.
     *
     * @return ConfigObject
     */
    public function getComponentConfigs()
    {
        return $this->_componentConfigs;
    }

    /**
     * Get the name of the current loaded environment.
     *
     * @return string
     * @throws BootstrapException
     */
    public function getCurrentEnvironmentName()
    {
        if (!empty($this->_currentEnvironmentName)) {
            return $this->_currentEnvironmentName;
        }

        // get current environments
        $environments = $this->_applicationConfig->get('Application.Environments', false);

        if($environments){
            // get current url
            $currentUrl = $this->str($this->httpRequest()->getCurrentUrl());

            // loop over all registered environments in the config, and try to match the current based on the domain
            foreach ($environments as $eName => $e) {
                if ($currentUrl->contains($e->Domain)) {
                    $this->_currentEnvironmentName = $eName;
                }
            }
        }

        if (empty($this->_currentEnvironmentName)) {
            $this->_currentEnvironmentName = 'Production';
        }

        return $this->_currentEnvironmentName;
    }

    /**
     * Get application config based on the current environment.
     *
     * @return ConfigObject
     * @throws BootstrapException
     */
    public function getCurrentEnvironmentConfig()
    {
        return $this->_applicationConfig->get('Application.Environments.' . $this->getCurrentEnvironmentName(), new ConfigObject([]));
    }

    /**
     * Loads application configuration.
     */
    private function _loadApplicationConfig()
    {
        // load the config
        try{
            $this->_applicationConfig = $this->config()->yaml($this->_applicationAbsolutePath . 'App/Config/App.yaml');
        }catch (\Exception $e){
            throw new BootstrapException('Unable to read app config file: '.$this->_applicationAbsolutePath . 'App/Config/App.yaml');
        }

    }

    /**
     * Initializes current environment.
     * Method detect the current environment and loads all the configurations from it.
     *
     * @throws BootstrapException
     * @throws \Webiny\Component\Config\ConfigException
     */
    private function _initializeEnvironment()
    {
        // validate the environment
        $environments = $this->_applicationConfig->get('Application.Environments', false);

        if($environments){
            // get the production environment
            $productionEnv = $environments->get('Production', false);
            if (!$productionEnv) {
                throw new BootstrapException('Production environment must always be defined in App/Config/App.yaml');
            }
        }

        // get the name of the current environment
        $currentEnvName = $this->getCurrentEnvironmentName();

        // load the production environment configs
        $this->_componentConfigs = $this->_loadConfigurations('Production');

        // check if the current env is different from Production
        if ($currentEnvName != 'Production') {
            $currentConfigs = $this->_loadConfigurations($currentEnvName);
            $this->_componentConfigs->mergeWith($currentConfigs);
        }
    }

    /**
     * Sets the error reporting based on the environment.
     *
     * @throws BootstrapException
     */
    private function _setErrorReporting()
    {
        // set error reporting
        $errorReporting = $this->_applicationConfig->get('Application.Environments.' . $this->getCurrentEnvironmentName(
                     ) . '.ErrorReporting', 'off'
        );
        if (strtolower($errorReporting) == 'on') {
            error_reporting(E_ALL);
        } else {
            error_reporting(E_ALL);
        }
    }

    /**
     * Registers the application namespace with the ClassLoader component.
     *
     * @throws BootstrapException
     */
    private function _registerAppNamespace()
    {
        // get app namespace
        $namespace = $this->_applicationConfig->get('Application.Namespace', false);
        if (!$namespace) {
            throw new BootstrapException('Unable to register application namespace. You must define the application namespace in your App.yaml config file.');
        }

        try{
            // register the namespace
            ClassLoader::getInstance()->registerMap([$namespace => $this->_applicationAbsolutePath.'App']);
        }catch (\Exception $e){
            throw new BootstrapException('Unable to register application ('.$namespace.' => '.$this->_applicationAbsolutePath.'App'.') namespace with ClassLoader.');
        }

    }

    /**
     * Initializes component configurations.
     * Component configurations are all the configurations inside the specific environment.
     * If configuration name matches a Webiny Framework component, then the configuration is automatically assigned
     * to that component.
     */
    private function _initializeComponentConfigurations()
    {
        foreach ($this->_webinyComponents as $c) {
            $componentConfig = $this->_componentConfigs->get($c, false);
            if ($componentConfig) {
                $class = 'Webiny\Component\\' . $c . '\\' . $c;
                $method = 'setConfig';
                try {
                    $class::$method($componentConfig);
                } catch (\Exception $e) {
                    // ignore it ... probably user-based component
                }
            }
        }
    }

    /**
     * Loads all the configurations from a specific environment.
     *
     * @param string $environment Environment name.
     *
     * @return ConfigObject
     * @throws \Webiny\Component\Config\ConfigException
     */
    private function _loadConfigurations($environment)
    {
        $configs = new ConfigObject([]);
        $configFolder = $this->_applicationAbsolutePath . 'App/Config/' . $environment;
        $h = scandir($configFolder);

        foreach ($h as $configFile) {
            if (strpos($configFile, 'yaml') === false) {
                continue;
            }

            $configs->mergeWith($this->config()->yaml($configFolder . DIRECTORY_SEPARATOR . $configFile));
        }

        return $configs;
    }
}