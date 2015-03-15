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
     * This is the request entry point, once the Bootstrap has been initialized.
     * The method initializes router and tries to call the callback assigned to the current url.
     * Method is call automatically from the Bootstrap class.
     *
     * @param string $url Url to route. If not set, the current url is used.
     *
     * @throws \Exception
     *
     * @return Dispatcher
     */
    public function initializeRouter($url = null)
    {
        // current url
        $currentUrl = is_null($url) ? $this->httpRequest()->getCurrentUrl() : $url;

        // init the router
        try {
            // try matching a custom route
            $result = $this->router()->match($currentUrl);

            if ($result) { // custom route matched
                // based on callback, route the request
                $callback = $result->getCallback();

                // namespace
                $ns = Bootstrap::getInstance()->getEnvironment()->getApplicationConfig()->get('Namespace', false);

                // extract callback parts
                $callbackData = $this->str($callback['Class'])->trimLeft('\\')->trimLeft($ns)->explode('\\')->val();

                if ($callbackData[1] == 'Modules' && $callbackData[3] == 'Controllers') {
                    // custom route, but still an MVC application
                    return $this->dispatchMvc($callbackData[2], $callbackData[4], $callback['Method'], $result->getParams());
                } else {
                    // custom route and custom callback (non MVC)
                    return $this->dispatchCustom($callback['Class'], $callback['Method'], $result->getParams());
                }
            } else { // fallback to the mvc router
                return $this->mvcRouter($this->httpRequest()->getCurrentUrl(true)->getPath());
            }
        } catch (\Exception $e) {
            throw $e;
        }

        throw new BootstrapException('No router matched the request.');
    }

    /**
     * This is the optional router that routes the MVC requests.
     *
     * @param string $request Current url path.
     *
     * @return Dispatcher
     *
     * @throws BootstrapException
     * @throws \Webiny\Component\StdLib\StdObject\ArrayObject\ArrayObjectException
     * @throws \Webiny\Component\StdLib\StdObject\StringObject\StringObjectException
     */
    public function mvcRouter($request)
    {
        // parse the request
        $request = $this->str($request)->trimLeft('/')->trimRight('/')->explode('/');
        if ($request->count() < 2) {
            throw new BootstrapException('Unable to route this request.');
        }

        // extract the url parts
        $module = $this->str($request[0]);
        $controller = $this->str($request[1]);
        $action = isset($request[2]) ? $this->str($request[2]) : $this->str('index');
        $params = [];
        if ($request->count() >= 4) {
            $params = $request->slice(3, $request->count())->val();
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
        return $this->dispatchMvc($module->val(), $controller->val(), $action->val(), $params);
    }

    /**
     * Issues the callback using the MVC dispatcher.
     *
     * @param string $module     Module name.
     * @param string $controller Controller name.
     * @param string $action     Action name.
     * @param array  $params     Parameter list.
     *
     * @return Dispatcher
     */
    private function dispatchMvc($module, $controller, $action, $params)
    {
        return Dispatcher::mvcDispatcher($module, $controller, $action, $params);
    }

    /**
     * In case of a custom route, we must use the custom dispatcher.
     *
     * @param string $className Fully qualified callback class name.
     * @param string $action    Class method name.
     * @param array  $params    Parameter list.
     *
     * @return Dispatcher
     */
    private function dispatchCustom($className, $action, $params)
    {
        return Dispatcher::customDispatcher($className, $action, $params);
    }
}