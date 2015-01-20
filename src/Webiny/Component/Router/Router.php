<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Router;

use Webiny\Component\Cache\CacheTrait;
use Webiny\Component\Config\ConfigObject;
use Webiny\Component\Cache\CacheStorage;
use Webiny\Component\Router\Loader\ConfigLoader;
use Webiny\Component\Router\Matcher\MatchedRoute;
use Webiny\Component\Router\Matcher\UrlMatcher;
use Webiny\Component\Router\Route\RouteCollection;
use Webiny\Component\ServiceManager\ServiceManagerTrait;
use Webiny\Component\StdLib\ComponentTrait;
use Webiny\Component\StdLib\SingletonTrait;
use Webiny\Component\StdLib\StdLibTrait;
use Webiny\Component\StdLib\StdObject\StdObjectWrapper;
use Webiny\Component\StdLib\StdObject\UrlObject\UrlObject;

/**
 * Router class is the main class that encapsulates all the Router components for easier usage.
 *
 * @package         Webiny\Component\Router
 */
class Router
{
    use StdLibTrait, ServiceManagerTrait, SingletonTrait, CacheTrait, ComponentTrait;

    /**
     * Key under which Router will cache its match results.
     */
    const CACHE_KEY = 'wf.component.router';

    /**
     * @var RouteCollection
     */
    private static $_routeCollection;

    /**
     * @var UrlGenerator
     */
    private $_urlGenerator;

    /**
     * @var UrlMatcher
     */
    private $_urlMatcher;

    /**
     * @var ConfigLoader
     */
    private $_loader;

    /**
     * @var bool|CacheStorage
     */
    private $_cache = false;


    /**
     * Tries to match the given url against current RouteCollection.
     *
     * @param string|UrlObject $url Url to match.
     *
     * @return MatchedRoute|bool MatchedRoute instance is returned if url was matched. Otherwise false is returned.
     */
    public function match($url)
    {
        if($this->isString($url)) {
            #$urlString = $this->str($url)->trimLeft('/')->trimRight('/')->val();
            $url = $this->url($url);
        } else {
            $url = StdObjectWrapper::isUrlObject($url) ? $url : $this->url('');
        }

        // get it from cache
        if(($result = $this->_loadFromCache('match.' . $url->val())) != false) {
            return $this->unserialize($result);
        }

        // try to match the url
        $result = $this->_urlMatcher->match($url);
        
        // cache it
        $cacheResult = $this->isArray($result) ? $this->serialize($result) : $result;
        $this->_saveToCache('match.' . $url->val(), $cacheResult);

        return $result;
    }

    /**
     * Execute callback from MatchedRoute and return result.<br>
     * Required callback structure is:
     * <code>
     * Callback:
     *     Class: \Your\Class
     *     Method: handle
     *     Static: true // (Optional, "false" by default)
     *
     *</code>
     * @param MatchedRoute $route
     *
     * @return mixed
     *
     * @throws RouterException
     */
    public function execute(MatchedRoute $route)
    {
        $callback = $route->getCallback();
        if($this->isString($callback)) {
            throw new RouterException(RouterException::STRING_CALLBACK_NOT_PARSABLE);
        }

        $callback = $this->arr($callback);
        $handlerClass = $callback->key('Class', false, true);
        $handlerMethod = $callback->key('Method', false, true);
        $staticMethod = StdObjectWrapper::toBool($callback->key('Static', false, true));

        if(!class_exists($handlerClass)) {
            throw new RouterException(RouterException::CALLBACK_CLASS_NOT_FOUND, [$handlerClass]);
        }

        if(!method_exists($handlerClass, $handlerMethod)) {
            throw new RouterException(RouterException::CALLBACK_CLASS_METHOD_NOT_FOUND, [
                $handlerMethod,
                $handlerClass
            ]);
        }

        if($staticMethod) {
            return forward_static_call_array([
                                                 $handlerClass,
                                                 $handlerMethod
                                             ], $route->getParams());
        }

        return call_user_func_array([
                                        new $handlerClass,
                                        $handlerMethod
                                    ], $route->getParams());
    }

    /**
     * Generate a url from a route.
     *
     * @param string $name       Name of the Route.
     * @param array  $parameters List of parameters that need to be replaced within the Route path.
     * @param bool   $absolute   Do you want to get the absolute url or relative. Default is absolute.
     *
     * @return string Generated url.
     * @throws RouterException
     */
    public function generate($name, $parameters = [], $absolute = true)
    {
        $key = 'generate.' . $name . implode('|', $parameters) . $absolute;
        if(($url = $this->_loadFromCache($key))) {
            return $url;
        }

        $url = $this->_urlGenerator->generate($name, $parameters, $absolute);
        $this->_saveToCache($key, $url);

        return $url;
    }

    /**
     * Sets the cache parameter.
     * If you don't want the Router to cache stuff, pass boolean false.
     *
     * @param bool|CacheStorage|string $cache Cache object, boolean false or name of a registered Cache service.
     *
     * @throws RouterException
     */
    public function setCache($cache)
    {
        $this->_cache = $cache;
        if($this->isBool($cache) && $cache === false) {
            $this->_cache = $cache;
        } else {
            if(is_object($cache)) {
                if($this->isInstanceOf($cache, '\Webiny\Component\Cache\CacheStorage')) {
                    $this->_cache = $cache;
                } else {
                    throw new RouterException('$cache must either be a boolean or instance of \Webiny\Component\Cache\CacheStorage.'
                    );
                }
            } else {
                $this->_cache = $this->cache($cache);
            }
        }
    }

    /**
     * Get the current cache parameter.
     *
     * @return bool|CacheStorage
     */
    public function getCache()
    {
        return $this->_cache;
    }

    /**
     * Overwrite the current route collection with the defined one.
     *
     * @param RouteCollection $routeCollection RouteCollection to install.
     */
    public function setRouteCollection(RouteCollection $routeCollection)
    {
        self::$_routeCollection = $routeCollection;
    }

    /**
     * Adds a route to the end of the current route collection.
     *
     * @param ConfigObject $routes An instance of ConfigObject holding the routes.
     *
     * @return $this
     */
    public function appendRoutes(ConfigObject $routes)
    {
        foreach ($routes as $name => &$routeConfig) {
            self::$_routeCollection->add($name, $this->_loader->processRoute($routeConfig));
        }

        return $this;
    }

    /**
     * Adds a route to the beginning of the current route collection.
     *
     * @param ConfigObject $routes An instance of ConfigObject holding the routes.
     *
     * @return $this
     */
    public function prependRoutes(ConfigObject $routes)
    {
        foreach ($routes as $name => &$routeConfig) {
            self::$_routeCollection->prepend($name, $this->_loader->processRoute($routeConfig));
        }

        return $this;
    }

    /**
     * @return RouteCollection
     */
    public static function getRouteCollection()
    {
        return self::$_routeCollection;
    }

    /**
     * Initializes the Route by reading the default config, registering routes and creating
     * necessary object instances.
     *
     */
    protected function _init()
    {
        $this->_loader = new ConfigLoader(self::getConfig()->get('Routes', new ConfigObject([])));
        self::$_routeCollection = $this->_loader->getRouteCollection();
        $this->_urlMatcher = new UrlMatcher();
        $this->_urlGenerator = new UrlGenerator();

        $this->setCache(self::getConfig()->get('Cache', false));
    }

    /**
     * Save the given value into cache.
     *
     * @param string $path  This is the cache key.
     * @param string $value This is the value that is going to be stored.
     */
    private function _saveToCache($path, $value)
    {
        if($this->getCache()) {
            $this->getCache()->save(self::CACHE_KEY . md5($path), $value, null, [
                                                                    '_wf',
                                                                    '_component',
                                                                    '_router'
                                                                ]
            );
        }
    }

    /**
     * Get a value from cache.
     *
     * @param string $path Cache identifier for which you wish to get the value.
     *
     * @return bool|string
     */
    private function _loadFromCache($path)
    {
        if($this->getCache()) {
            return $this->getCache()->read(self::CACHE_KEY . md5($path));
        }

        return false;
    }

    /**
     * Post setConfig callback
     */
    protected static function postSetConfig()
    {
        if(self::getConfig()->get('Cache', false)) {
            Router::getInstance()->setCache(self::getConfig()->get('Cache'));
        }
    }

}