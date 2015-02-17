<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Bootstrap;

use Webiny\Component\Bootstrap\ApplicationClasses\Application;
use Webiny\Component\ClassLoader\ClassLoader;
use Webiny\Component\StdLib\StdObjectTrait;

/**
 * Bootstrap dispatcher class.
 * Receives the callback data from the Router and issues the callback.
 * Dispatcher works with standard MVC callback, or with a custom class based callback.
 *
 * @package         Webiny\Component\Bootstrap
 */
class Dispatcher
{
    use StdObjectTrait;

    /**
     * @var string Module name (MVC Only)
     */
    private $_module;

    /**
     * @var string Controller name (MVC Only)
     */
    private $_controller;

    /**
     * @var string Action name.
     */
    private $_action;

    /**
     * @var array Array of parameters that will be passed to the callback method.
     */
    private $_params = [];

    /**
     * @var string Callback class name.
     */
    private $_className;


    /**
     * Creates a Dispatcher instance from MVC parameters.
     *
     * @param string $module     Module name.
     * @param string $controller Controller name.
     * @param string $action     Action name.
     * @param array  $params     Parameters.
     *
     * @return Dispatcher
     */
    public static function mvcDispatcher($module, $controller, $action, $params)
    {
        $dispatcher = new self;
        $dispatcher->setModule($module);
        $dispatcher->setController($controller);
        $dispatcher->setAction($action);
        $dispatcher->setParams($params);

        // get current application config
        $applicationConfig = Bootstrap::getInstance()->getEnvironment()->getApplicationConfig();

        // build the class name
        $className = '\\' . $applicationConfig->Namespace . '\Modules\\' . $dispatcher->getModule(
            ) . '\Controllers\\' . $dispatcher->getController();
        $dispatcher->setClassName($className);

        return $dispatcher;
    }

    /**
     * Creates a Dispatcher instance from a class name.
     *
     * @param string $className Fully qualified class name.
     * @param string $action    Action name.
     * @param array  $params    Parameters.
     *
     * @return Dispatcher
     */
    public static function customDispatcher($className, $action, $params)
    {
        $dispatcher = new self;
        $dispatcher->setClassName($className);
        $dispatcher->setAction($action);
        $dispatcher->setParams($params);

        return $dispatcher;
    }

    /**
     * Sets the module name.
     *
     * @param string $module Module name.
     */
    public function setModule($module)
    {
        $this->_module = $module;
    }

    /**
     * Returns the module name.
     *
     * @return string
     */
    public function getModule()
    {
        return $this->_module;
    }

    /**
     * Sets the controller name.
     *
     * @param string $controller Controller name.
     */
    public function setController($controller)
    {
        $this->_controller = $controller;
    }

    /**
     * Returns the controller name.
     *
     * @return string
     */
    public function getController()
    {
        return $this->_controller;
    }

    /**
     * Sets the action name.
     *
     * @param string $action Action name.
     *
     * @throws \Webiny\Component\StdLib\StdObject\StringObject\StringObjectException
     */
    public function setAction($action)
    {
        $this->_action = $this->str($action)->replace('Action', '')->val();
    }

    /**
     * Returns action name.
     *
     * @return string
     */
    public function getAction()
    {
        return $this->_action;
    }

    /**
     * Sets the callback parameters.
     *
     * @param array $params List of parameters.
     */
    public function setParams(array $params)
    {
        $this->_params = $params;
    }

    /**
     * Returns the current list of parameters.
     *
     * @return array
     */
    public function getParams()
    {
        return $this->_params;
    }

    /**
     * Sets the class name. Note, this will overwrite the current class name, also valid in case of MVC callback.
     *
     * @param string $className Fully qualified class name.
     *
     * @throws \Exception | BootstrapException
     */
    public function setClassName($className)
    {
        try {
            $classFilename = ClassLoader::getInstance()->findClass($className);
            if (!file_exists($classFilename)) {
                throw new BootstrapException('The provided callback class "' . $className . '" does not exist.');
            } else {
                $this->_className = $className;
            }
        } catch (\Exception $e) {
            throw $e;
        }

    }

    /**
     * Returns the current class name.
     *
     * @return string
     */
    public function getClassName()
    {
        return $this->_className;
    }

    /**
     * Calls the defined action on the current class name, passing along the parameters.
     *
     * @throws BootstrapException
     */
    public function issueCallback()
    {
        $instance = $this->_getCallbackClassInstance();

        // call the controller
        call_user_func_array([
                                 $instance,
                                 $this->getAction() . 'Action'
                             ], $this->getParams()
        );

        $response = $instance->app()->httpResponse();
        if ($response) {
            $response->send();
        }
    }

    /**
     * Creates class instance from the defined class name.
     * Method automatically also tries to set the view template - only in case of an MVC callback.
     * In all cases the callback class must implement the AppTrait, because the method also calls the setUp method on
     * the class instance.
     *
     * @return mixed
     * @throws BootstrapException
     */
    private function _getCallbackClassInstance()
    {
        // create and validate the instance
        $className = $this->getClassName();
        $instance = new $className();

        // check that the app implements the trait
        // is we use the class_traits method, we only get the traits of the current level, we don't get the traits
        // if the class extends another class that actually implements the trait
        if (!method_exists($instance, 'setAppInstance')) {
            if (!isset($traits['Webiny\Component\Bootstrap\ApplicationTraits\AppTrait'])) {
                throw new BootstrapException('Class "' . $className . '" must use "Webiny\Component\Bootstrap\ApplicationTraits\AppTrait" trait.'
                );
            }
        }

        $app = $this->_getApplicationInstance();

        // assign the app to the app trait
        $instance->setAppInstance($app);

        // set the instance template
        $this->_setTemplate($instance);

        // call the setUp method
        $instance->setUp();

        return $instance;
    }

    /**
     * Tries to set the view template for the given $instance.
     *
     * @param mixed $instance Instance provided from _getCallbackClassInstance method.
     */
    private function _setTemplate($instance)
    {
        // we don't know what is the template extension, so we need to match by action name
        if (!empty($this->getModule()) && $instance->app()->view()->getAutoload()) {
            $templateDir = Bootstrap::getInstance()->getEnvironment()->getApplicationAbsolutePath(
                ) . 'App/Modules/' . $this->getModule() . '/Views/' . $this->getController();

            $templates = scandir($templateDir);
            if ($templates) {
                $tplFilename = '';

                $tplActionName = $this->str($this->getAction())->charFirstUpper()->val();

                foreach ($templates as $tpl) {
                    if (strpos($tpl, $tplActionName . '.') !== false) {
                        $tplFilename = $tpl;
                        break;
                    }
                }

                if ($tplFilename != '') {
                    $instance->app()->view()->setTemplate('../Modules/' . $this->getModule(
                                                          ) . '/Views/' . $this->getController() . '/' . $tplFilename
                    );
                }
            }
        }
    }

    /**
     * Create a new Application instance. The method also sets the current configuration and environment inside the
     * Application instance.
     *
     * @return Application
     * @throws BootstrapException
     */
    private function _getApplicationInstance()
    {
        // create the app instance
        $app = new Application(Bootstrap::getInstance()->getEnvironment());

        return $app;
    }
}