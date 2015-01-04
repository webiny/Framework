<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Bootstrap;

use Webiny\Component\Http\HttpTrait;
use Webiny\Component\Router\RouterTrait;
use Webiny\Component\StdLib\SingletonTrait;
use Webiny\Component\StdLib\StdObjectTrait;

/**
 * Bootstrap router class.
 * Initializes the Router and routes the requests to Dispatcher.
 *
 * @package         Webiny\Component\Bootstrap
 */
class Router
{
    use SingletonTrait, StdObjectTrait, HttpTrait, RouterTrait;

    /**
     * This is the request entry point, once the Boostrap has been initialized.
     * The method initializes router and tries to call the callback assigned to the current url.
     * Method is call automatically from the Bootstrap class.
     *
     * @throws \Exception
     */
    public function initializeRouter()
    {
        // current url
        $currentUrl = $this->httpRequest()->getCurrentUrl();

        // init the router
        try {
            $result = $this->router()->match($currentUrl);

            if (!$result) {
                throw new BootstrapException('Current url did not match any route.');
            }

            // based on callback, route the request
            $callback = $result->getCallback();

            if ($callback['Class'] == 'Webiny\Component\Bootstrap\Router' && $callback['Method'] == 'mvcRouter') {
                // if bootstrap router is the assigned callback class, we do the standard MVC routing
                $this->router()->execute($result);
            } else {
                // custom callback -> not the internal mvcRouter

                // extract callback parts
                $callbackData = $this->str($callback['Class'])->trimLeft('\\')->explode('\\')->val();

                if ($callbackData[1] == 'Modules' && $callbackData[3] == 'Controllers') {
                    // custom route, but still a MVC application
                    $this->_dispatchMvc($callbackData[2], $callbackData[4], $callback['Method'], $result->getParams());
                } else {
                    // custom route and custom callback (non MVC)
                    $this->_dispatchCustom($callback['Class'], $callback['Method'], $result->getParams());
                }
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * This is the optional router that routes the MVC requests.
     *
     * @param string $request Current url path.
     *
     * @throws BootstrapException
     * @throws \Webiny\Component\StdLib\StdObject\ArrayObject\ArrayObjectException
     * @throws \Webiny\Component\StdLib\StdObject\StringObject\StringObjectException
     */
    public function mvcRouter($request)
    {
        // parse the request
        $request = $this->str($request)->explode('/');
        if ($request->count() < 2) {
            throw new BootstrapException('Unable to route this request.');
        }

        // extract the url parts
        $module = $this->str($request[0]);
        $controller = $this->str($request[1]);
        $action = isset($request[2]) ? $this->str($request[2]) : $this->str('index');
        $params = [];
        if ($request->count() >= 4) {
            $params = $request->slice(3, null);
        }

        // check if we need to normalize the request parts
        if ($module->contains('-')) {
            $module = $module->replace('-', ' ')->caseWordUpper()->replace(' ', '');
        } else {
            $module = $module->charFirstUpper();
        }
        if ($controller->contains('-')) {
            $controller = $controller->replace('-', ' ')->caseWordUpper()->replace(' ', '');
        } else {
            $controller = $controller->charFirstUpper();
        }
        if ($action->contains('-')) {
            $action = $action->replace('-', ' ')->caseWordUpper()->replace(' ', '');
        }

        // call the dispatcher
        $this->_dispatchMvc($module, $controller, $action, $params);
    }

    /**
     * Issues the callback using the MVC dispatcher.
     *
     * @param string $module     Module name.
     * @param string $controller Controller name.
     * @param string $action     Action name.
     * @param array  $params     Parameter list.
     */
    private function _dispatchMvc($module, $controller, $action, $params)
    {
        Dispatcher::mvcDispatcher($module, $controller, $action, $params)->issueCallback();
    }

    /**
     * In case of a custom route, we must use the custom dispatcher.
     *
     * @param string $className Fully qualified callback class name.
     * @param string $action    Class method name.
     * @param array  $params    Parameter list.
     */
    private function _dispatchCustom($className, $action, $params)
    {
        Dispatcher::customDispatcher($className, $action, $params)->issueCallback();
    }
}