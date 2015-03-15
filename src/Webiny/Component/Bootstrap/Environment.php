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
    private $applicationConfig;

    /**
     * @var string Absolute path to the root of users application.
     */
    private $applicationAbsolutePath;

    /**
     * @var string Name of the current environment.
     */
    private $currentEnvironmentName;

    /**
     * @var ConfigObject List of loaded configurations from the current environment.
     */
    private $componentConfigs;

    /**
     * @var array List of Webiny Framework components, defined in order that we don't have problems with dependencies
     * between the components.
     */
    private $webinyComponents = [
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
        $this->applicationAbsolutePath = $applicationAbsolutePath;

        $this->loadApplicationConfig();
        $this->registerAppNamespace();
        $this->initializeEnvironmentInternal();
        $this->initializeComponentConfigurations();
        $this->setErrorReporting();
    }

    /**
     * Get current application configuration.
     *
     * @return ConfigObject
     */
    public function getApplicationConfig()
    {
        return $this->applicationConfig->Application;
    }

    /**
     * Get the current application absolute path.
     *
     * @return string
     */
    public function getApplicationAbsolutePath()
    {
        return $this->applicationAbsolutePath;
    }

    /**
     * Get all loaded configurations.
     *
     * @return ConfigObject
     */
    public function getComponentConfigs()
    {
        return $this->componentConfigs;
    }

    /**
     * Get the name of the current loaded environment.
     *
     * @return string
     * @throws BootstrapException
     */
    public function getCurrentEnvironmentName()
    {
        if (!empty($this->currentEnvironmentName)) {
            return $this->currentEnvironmentName;
        }

        // get current environments
        $environments = $this->applicationConfig->get('Application.Environments', false);

        if($environments){
            // get current url
            $currentUrl = $this->str($this->httpRequest()->getCurrentUrl());

            // loop over all registered environments in the config, and try to match the current based on the domain
            foreach ($environments as $eName => $e) {
                if ($currentUrl->contains($e->Domain)) {
                    $this->currentEnvironmentName = $eName;
                }
            }
        }

        if (empty($this->currentEnvironmentName)) {
            $this->currentEnvironmentName = 'Production';
        }

        return $this->currentEnvironmentName;
    }

    /**
     * Get application config based on the current environment.
     *
     * @return ConfigObject
     * @throws BootstrapException
     */
    public function getCurrentEnvironmentConfig()
    {
        return $this->applicationConfig->get('Application.Environments.' . $this->getCurrentEnvironmentName(), new ConfigObject([]));
    }

    /**
     * Loads application configuration.
     */
    private function loadApplicationConfig()
    {
        // load the config
        try{
            $this->applicationConfig = $this->config()->yaml($this->applicationAbsolutePath . 'App/Config/App.yaml');
        }catch (\Exception $e){
            throw new BootstrapException('Unable to read app config file: '.$this->applicationAbsolutePath . 'App/Config/App.yaml');
        }

    }

    /**
     * Initializes current environment.
     * Method detect the current environment and loads all the configurations from it.
     *
     * @throws BootstrapException
     * @throws \Webiny\Component\Config\ConfigException
     */
    private function initializeEnvironmentInternal()
    {
        // validate the environment
        $environments = $this->applicationConfig->get('Application.Environments', false);

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
        $this->componentConfigs = $this->loadConfigurations('Production');

        // check if the current env is different from Production
        if ($currentEnvName != 'Production') {
            $currentConfigs = $this->loadConfigurations($currentEnvName);
            $this->componentConfigs->mergeWith($currentConfigs);
        }
    }

    /**
     * Sets the error reporting based on the environment.
     *
     * @throws BootstrapException
     */
    private function setErrorReporting()
    {
        // set error reporting
        $errorReporting = $this->applicationConfig->get('Application.Environments.' . $this->getCurrentEnvironmentName(
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
    private function registerAppNamespace()
    {
        // get app namespace
        $namespace = $this->applicationConfig->get('Application.Namespace', false);
        if (!$namespace) {
            throw new BootstrapException('Unable to register application namespace. You must define the application namespace in your App.yaml config file.');
        }

        try{
            // register the namespace
            ClassLoader::getInstance()->registerMap([$namespace => $this->applicationAbsolutePath.'App']);
        }catch (\Exception $e){
            throw new BootstrapException('Unable to register application ('.$namespace.' => '.$this->applicationAbsolutePath.'App'.') namespace with ClassLoader.');
        }

    }

    /**
     * Initializes component configurations.
     * Component configurations are all the configurations inside the specific environment.
     * If configuration name matches a Webiny Framework component, then the configuration is automatically assigned
     * to that component.
     */
    private function initializeComponentConfigurations()
    {
        foreach ($this->webinyComponents as $c) {
            $componentConfig = $this->componentConfigs->get($c, false);
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
    private function loadConfigurations($environment)
    {
        $configs = new ConfigObject([]);
        $configFolder = $this->applicationAbsolutePath . 'App/Config/' . $environment;
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