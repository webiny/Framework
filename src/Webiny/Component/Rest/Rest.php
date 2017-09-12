<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Rest;

use Webiny\Component\Config\ConfigObject;
use Webiny\Component\Http\HttpTrait;
use Webiny\Component\Rest\Compiler\Cache;
use Webiny\Component\Rest\Compiler\CacheDrivers\ArrayDriver;
use Webiny\Component\Rest\Compiler\CacheDrivers\CacheDriverInterface;
use Webiny\Component\Rest\Compiler\CacheDrivers\FilesystemDriver;
use Webiny\Component\Rest\Compiler\Compiler;
use Webiny\Component\Rest\Parser\Parser;
use Webiny\Component\Rest\Response\Router;
use Webiny\Component\Router\Matcher\MatchedRoute;
use Webiny\Component\Router\Route\Route;
use Webiny\Component\Router\RouterTrait;
use Webiny\Component\StdLib\ComponentTrait;
use Webiny\Component\StdLib\Exception\Exception;
use Webiny\Component\StdLib\FactoryLoaderTrait;
use Webiny\Component\StdLib\StdObjectTrait;

/**
 * Rest class provides the main methods for registering and building API requests.
 *
 * @package    Webiny\Component\Rest
 */
class Rest
{
    use ComponentTrait, RouterTrait, HttpTrait, StdObjectTrait, FactoryLoaderTrait;

    /**
     * Environment constants
     */
    const ENV_DEVELOPMENT = 'development';
    const ENV_PRODUCTION = 'production';

    /**
     * Default cache drivers
     */
    const DEV_CACHE_DRIVER = ArrayDriver::class;
    const PROD_CACHE_DRIVER = FilesystemDriver::class;

    /**
     * @var string Name of the api configuration.
     */
    private $api;

    /**
     * @var string Environment, can either be 'development' or 'production'.
     */
    private $environment = 'production';

    /**
     * @var mixed|\Webiny\Component\Config\ConfigObject Rest api specific configuration.
     */
    private $config;

    /**
     * @var string Name of the class that has been registered.
     */
    private $class;

    /**
     * @var bool Should the url parts be normalized.
     */
    private $normalize;

    /**
     * @var \Webiny\Component\Rest\Compiler\Cache
     */
    private $cacheInstance;

    /**
     * @var string Url passed from initRest method.
     */
    private static $url;

    /**
     * @var string HTTP method passed from the initRest method.
     */
    private static $method;


    /**
     * Initializes the current Rest configuration, tries to match the current URL with the defined Path.
     * If match was successful, an instance of Rest class is returned, otherwise false.
     *
     * @param string $api Api configuration Name
     * @param string $url Url on which the to match. Leave blank to use the current url.
     * @param string $method Name of the HTTP method that will be used to match the request.
     *                       Leave blank to use the method from the current HTTP request.
     *
     * @return bool|Rest
     * @throws RestException
     * @throws \Webiny\Component\StdLib\StdObject\StringObject\StringObjectException
     */
    public static function initRest($api, $url = '', $method = '')
    {
        $config = self::getConfig()->get($api, false);

        if (!$config) {
            throw new RestException('Configuration for "' . $api . '" not found.');
        }

        // check if we have the Path defined
        $path = $config->get('Router.Path', false);
        if (!$path) {
            throw new RestException('Router.Path is not defined for "' . $api . '" api.');
        }

        // create the route for Router component
        $route = [
            'WebinyRest' . $api => [
                'Path'     => self::str($path)->trimRight('/')->append('/{webiny_rest_params}')->val(),
                'Callback' => 'null',
                'Options'  => [
                    'webiny_rest_params' => [
                        'Pattern' => '.*?'
                    ]
                ]
            ]
        ];

        // call the router and match the request
        // we must append some dummy content at the end of the url, because in case of a default API method, and since
        // we have added an additional pattern to the url, the url will not match
        self::router()->prependRoutes(new ConfigObject($route));
        if ($url == '') {
            $result = self::router()->match(self::str(self::httpRequest()->getCurrentUrl(true)->getPath())
                                                ->trimRight('/')
                                                ->append('/_w_rest/_foo')
                                                ->val());
        } else {
            $result = self::router()->match(self::str($url)->trimRight('/')->append('/_w_rest/_foo')->val());
        }

        if (!$result) {
            return false;
        }

        self::$url = $url;
        self::$method = $method;

        return self::processRouterResponse($result, $config, $api);
    }

    /**
     * Internal static method that is called when initRest method matches a URL agains the Path.
     * This method then processes that matched response and then creates and returns a Rest instance back to iniRest.
     *
     * @param MatchedRoute $matchedRoute The matched route.
     * @param ConfigObject $config Current api config.
     * @param string       $api Current api name.
     *
     * @return Rest
     * @throws \Webiny\Component\StdLib\StdObject\StringObject\StringObjectException
     */
    private static function processRouterResponse(MatchedRoute $matchedRoute, ConfigObject $config, $api)
    {
        // based on the matched route create the class name
        $className = self::str($config->get('Router.Class'))->trimLeft('\\')->prepend('\\');
        $normalize = $config->get('Router.Normalize', false);
        $matchedParams = $matchedRoute->getParams();
        foreach ($matchedParams as $mpName => $mpParam) {
            if ($normalize) {
                $mpParam = self::str($mpParam)->replace('-', ' ')->caseWordUpper()->replace(' ', '');
            }

            $className->replace('{' . $mpName . '}', $mpParam);
        }

        // return the new rest instance
        return new self($api, $className->val());
    }

    /**
     * Base constructor.
     *
     * @param string $api Name of the api configuration.
     * @param string $class Name of the class that will been registered with the api.
     *
     * @throws RestException
     */
    public function __construct($api, $class)
    {
        $this->config = $this->getConfig()->get($api, false);
        if (!$this->config) {
            throw new RestException('Configuration for "' . $api . '" not found.');
        }

        $this->setEnvironment($this->config->get('Environment', 'production'));
        $this->initializeCache();

        $this->api = $api;
        $this->class = $class;
        $this->normalize = $this->config->get('Router.Normalize', false);

        $this->registerClass();
    }

    /**
     * Set the component environment.
     * If it's development, the component will output additional information inside the debug header.
     *
     * @param string $env Can either be 'development' or 'production'
     *
     * @throws RestException
     */
    public function setEnvironment($env = self::ENV_PRODUCTION)
    {
        if ($env != self::ENV_DEVELOPMENT && $env != self::ENV_PRODUCTION) {
            throw new RestException('Unknown environment "' . $env . '".');
        }

        $this->environment = $env;
    }

    /**
     * Get the name of current environment.
     *
     * @return string
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * Processes the current request and returns an instance of CallbackResult.
     *
     * @return bool|Response\CallbackResult
     * @throws RestException
     */
    public function processRequest()
    {
        try {
            $router = new Router($this->api, $this->class, $this->normalize, $this->cacheInstance);

            // check if url is set via the initRest method
            if (!empty(self::$url)) {
                $router->setUrl(self::$url);
            }

            // check if the method vas set via initRest method
            if (!empty(self::$method)) {
                $router->setHttpMethod(self::$method);
            }

            return $router->processRequest();
        } catch (\Exception $e) {
            $exception = new RestException('Unable to process request for class "' . $this->class . '". ' . $e->getMessage());
            $exception->setRequestedClass($this->class);
            throw $exception;
        }
    }

    /**
     * Returns true if current environment is 'development'.
     *
     * @return bool
     */
    private function isDevelopment()
    {
        return ($this->environment == 'development') ? true : false;
    }

    /**
     * Registers the class and creates a compile cache version of it.
     *
     * @throws RestException
     */
    private function registerClass()
    {
        try {
            if (!$this->cacheInstance->isCacheValid($this->api, $this->class) || $this->isDevelopment()) {
                $this->parseClass();
            }
        } catch (\Exception $e) {
            $exception = new RestException('Unable to register class "' . $this->class . '". ' . $e->getMessage());
            $exception->setRequestedClass($this->class);
            throw $exception;
        }
    }

    /**
     * Calls the Parser to parse the class and
     * then Compiler to create a compiled cache file of the parsed class.
     */
    private function parseClass()
    {
        $parser = new Parser();
        $parsedApi = $parser->parseApi($this->class, $this->normalize);

        // in development we always write cache
        $writer = new Compiler($this->api, $this->normalize, $this->cacheInstance);
        $writer->writeCacheFiles($parsedApi);
    }

    /**
     * Initializes the compiler cache driver.
     *
     * @throws Exception
     * @throws \Exception
     */
    private function initializeCache()
    {
        // get driver
        if (!($driver = $this->config->get('CompilerCacheDriver', false))) {
            // default driver
            if ($this->isDevelopment()) {
                $driver = self::DEV_CACHE_DRIVER;
            } else {
                $driver = self::PROD_CACHE_DRIVER;
            }
        }

        // create driver instance
        try {
            $instance = $this->factory($driver, CacheDriverInterface::class);
            $this->cacheInstance = new Cache($instance);
        } catch (Exception $e) {
            throw $e;
        }
    }
}