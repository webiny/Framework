<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Bootstrap;

use Webiny\Component\Bootstrap\ApplicationClasses\Application;
use Webiny\Component\Config\ConfigObject;
use Webiny\Component\Config\ConfigTrait;
use Webiny\Component\Http\HttpTrait;
use Webiny\Component\Router\RouterTrait;
use Webiny\Component\StdLib\StdLibTrait;

/**
 * Bootstrap base class.
 *
 * @package         Webiny\Component\Bootstrap
 */
class Bootstrap
{
    use ConfigTrait, HttpTrait, RouterTrait, StdLibTrait;

    private static $_appConfig;
    private static $_componentConfigs;
    private static $_absolutePath;
    private static $_env;

    public static function runApplication($rootPath)
    {
        // save the root path
        self::$_absolutePath = realpath($rootPath) . DIRECTORY_SEPARATOR;

        self::_initializeEnvironment();

        self::_initializeComponents();

        self::_initializeRouter();
    }

    public static function mvcRouter($request)
    {
        // parse the request
        $request = explode('/', $request);
        if (count($request) < 2) {
            throw new BootstrapException('Unable to route this request.');
        }

        $module = $request[0];
        $controller = $request[1];
        $action = isset($request[2]) ? $request[2] : 'index';
        $params = [];
        if (count($request) >= 4) {
            $params = array_slice($request, 3);
        }

        // check if we need to normalize the file names
        if (strpos($module, '-') !== false) {
            $module = self::str($module)->replace('-', ' ')->caseWordUpper()->replace(' ', '')->val();
        } else {
            $module = self::str($module)->charFirstUpper();
        }
        if (strpos($controller, '-') !== false) {
            $controller = self::str($controller)->replace('-', ' ')->caseWordUpper()->replace(' ', '')->val();
        } else {
            $controller = self::str($controller)->charFirstUpper();
        }
        if (strpos($action, '-') !== false) {
            $action = self::str($action)->replace('-', ' ')->caseWordUpper()->replace(' ', '')->val();
        }

        self::_callModule($module, $controller, $action, $params);
    }

    private static function _callModule($module, $controller, $action, $params)
    {
        // build the class name
        $className = '\\' . self::$_appConfig->Application->Namespace . '\Modules\\' . $module . '\Controllers\\' . $controller;

        // create and validate the instance
        $instance = new $className();

        // create the app instance
        $app = new Application(self::$_appConfig, self::$_componentConfigs, self::$_env);

        // check that the app implements the trait
        // is we use the class_traits method, we only get the traits of the current level, we don't get the traits
        // if the class extends another class that actually implements the trait
        if (!method_exists($instance, 'setAppInstance')) {
            if (!isset($traits['Webiny\Component\Bootstrap\ApplicationTraits\AppTrait'])) {
                throw new BootstrapException('Your controller must use "Webiny\Component\Bootstrap\ApplicationTraits\AppTrait" trait.'
                );
            }
        }
        $app->setAbsolutePath(self::$_absolutePath);

        // assign the app to the app trait
        $instance->setAppInstance($app);

        // call the setUp method
        $instance->setUp();

        // we don't know what is the template extension, so we need to match by action name
        if ($instance->app()->view()->getAutoload()) {
            $templates = @scandir(self::$_absolutePath . 'App/Modules/' . $module . '/Views/' . $controller);
            if ($templates) {
                $tplFilename = '';
                $tplActionName = self::str($action)->charFirstUpper()->val();
                foreach ($templates as $tpl) {
                    if (strpos($tpl, $tplActionName . '.') !== false) {
                        $tplFilename = $tpl;
                        break;
                    }
                }
                if ($tplFilename != '') {
                    $instance->app()
                             ->view()
                             ->setTemplate('../Modules/' . $module . '/Views/' . $controller . '/' . $tplFilename);
                }
            }
        }

        // call the controller
        call_user_func_array([
                                 $instance,
                                 $action . 'Action'
                             ], $params
        );
    }

    private static function _initializeEnvironment()
    {
        // load the config
        self::$_appConfig = self::config()->yaml(self::$_absolutePath . 'App/Config/App.yaml');

        // validate the environment
        $environments = self::$_appConfig->get('Application.Environments');

        // get the production environment
        $productionEnv = $environments->get('Production', false);
        if (!$productionEnv) {
            throw new BootstrapException('Production environment must always be defined in App/Config/App.yaml');
        }

        // check on which environment we are currently
        $currentUrl = self::httpRequest()->getCurrentUrl();

        foreach ($environments as $eName => $e) {
            if (stripos($currentUrl, $e->Domain) !== false) {
                self::$_env = $eName;
            }
        }
        if (empty(self::$_env)) {
            throw new BootstrapException('Unable to bootstrap your application. None of the environments matched your domain name in App/Config/App.yaml'
            );
        }

        // load the production environment configs
        self::$_componentConfigs = self::_loadConfigurations('Production');

        // check if the current env is different from Production
        if (self::$_env != 'Production') {
            $currentConfigs = self::_loadConfigurations(self::$_env);
            self::$_componentConfigs->mergeWith($currentConfigs);
        }

        // set error reporting
        $errorReporting = self::$_appConfig->get('Application.Environments.' . self::$_env . '.ErrorReporting', 'off');
        if (strtolower($errorReporting) == 'on') {
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
        } else {
            error_reporting(0);
            ini_set('display_errors', 0);
        }
    }

    private static function _initializeComponents()
    {
        foreach (self::$_componentConfigs as $component => $config) {
            $class = 'Webiny\Component\\' . $component . '\\' . $component;
            $method = 'setConfig';
            try {
                $class::$method($config);
            } catch (\Exception $e) {
                // ignore it ... probably user-based component
            }
        }
    }

    private static function _initializeRouter()
    {
        // current url
        $currentUrl = self::httpRequest()->getCurrentUrl();

        // init the router
        try {
            $result = self::router()->match($currentUrl);

            if (!$result) {
                throw new BootstrapException('Current url did not match any route.');
            }

            $callback = $result->getCallback();
            if ($callback['Class'] == 'Webiny\Component\Bootstrap\Bootstrap' && $callback['Method'] == 'mvcRouter') {
                self::router()->execute($result);
            } else {
                $callbackData = explode('\\', ltrim($callback['Class'], '\\'));
                if ($callbackData[1] == 'Modules') {
                    // mvc routing -> not based on the url, but on the callback class structure
                    $method = str_replace('Action', '', $callback['Method']);
                    self::_callModule($callbackData[2], $callbackData[4], $method, $result->getParams());
                }else{
                    // custom routing
                    self::router()->execute($result);
                }
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }


    private static function _loadConfigurations($environment)
    {
        $configs = new ConfigObject([]);
        $configFolder = self::$_absolutePath . 'App/Config/' . $environment;
        $h = scandir($configFolder);

        foreach ($h as $configFile) {
            if (strpos($configFile, 'yaml') === false) {
                continue;
            }

            $configs->mergeWith(self::config()->yaml($configFolder . DIRECTORY_SEPARATOR . $configFile));
        }

        return $configs;
    }
}