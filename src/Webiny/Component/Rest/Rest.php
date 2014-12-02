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
use Webiny\Component\Rest\Compiler\Compiler;
use Webiny\Component\Rest\Parser\Parser;
use Webiny\Component\Rest\Response\Router;
use Webiny\Component\Router\Matcher\MatchedRoute;
use Webiny\Component\Router\Route\Route;
use Webiny\Component\Router\RouterTrait;
use Webiny\Component\StdLib\ComponentTrait;
use Webiny\Component\StdLib\StdObjectTrait;

/**
 * Rest class provides the main methods for registering and building API requests.
 *
 * @package    Webiny\Component\Rest
 */
class Rest
{
    use ComponentTrait, RouterTrait, HttpTrait, StdObjectTrait;

    /**
     * Environment constants
     */
    const ENV_DEVELOPMENT = 'development';
    const ENV_PRODUCTION = 'production';

    /**
     * @var string Name of the api configuration.
     */
    private $_api;

    /**
     * @var string Environment, can either be 'development' or 'production'.
     */
    private $_environment = 'production';

    /**
     * @var mixed|\Webiny\Component\Config\ConfigObject Rest api specific configuration.
     */
    private $_config;

    /**
     * @var string Name of the class that has been registered.
     */
    private $_class;

    /**
     * @var bool Should the url parts be normalized.
     */
    private $_normalize;


    /**
     * Initializes the current Rest configuration, tries to match the current URL with the defined Path.
     * If match was successful, an instance of Rest class is returned, otherwise false.
     *
     * @param string $api Api configuration Name
     * @param string $url Url on which the to match. Leave blank to use the current url.
     *
     * @return bool|Rest
     * @throws RestException
     * @throws \Webiny\Component\StdLib\StdObject\StringObject\StringObjectException
     */
    static public function initRest($api, $url = '')
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
                                                ->val()
            );
        } else {
            $result = self::router()->match(self::str($url)->trimRight('/')->append('/_w_rest/_foo')->val());
        }

        if (!$result) {
            return false;
        }

        return self::_processRouterResponse($result, $config, $api);
    }

    /**
     * Internal static method that is called when initRest method matches a URL agains the Path.
     * This method then processes that matched response and then creates and returns a Rest instance back to iniRest.
     *
     * @param MatchedRoute $matchedRoute The matched route.
     * @param ConfigObject $config       Current api config.
     * @param string       $api          Current api name.
     *
     * @return Rest
     * @throws \Webiny\Component\StdLib\StdObject\StringObject\StringObjectException
     */
    static private function _processRouterResponse(MatchedRoute $matchedRoute, ConfigObject $config, $api)
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
     * @param string $api   Name of the api configuration.
     * @param string $class Name of the class that will been registered with the api.
     *
     * @throws RestException
     */
    public function __construct($api, $class)
    {
        $this->_config = $this->getConfig()->get($api, false);
        if (!$this->_config) {
            throw new RestException('Configuration for "' . $api . '" not found.');
        }

        $this->setEnvironment($this->_config->get('Environment', 'production'));

        $this->_api = $api;
        $this->_class = $class;
        $this->_normalize = $this->_config->get('Router.Normalize', false);

        $this->_registerClass();
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

        $this->_environment = $env;
    }

    /**
     * Get the name of current environment.
     *
     * @return string
     */
    public function getEnvironment()
    {
        return $this->_environment;
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
            $router = new Router($this->_api, $this->_class, $this->_normalize);

            return $router->processRequest();
        } catch (\Exception $e) {
            throw new RestException('Unable to process request for class "' . $this->_class . '". ' . $e->getMessage());
        }
    }

    /**
     * Returns true if current environment is 'development'.
     *
     * @return bool
     */
    private function _isDevelopment()
    {
        return ($this->_environment == 'development') ? true : false;
    }

    /**
     * Registers the class and creates a compile cache version of it.
     *
     * @throws RestException
     */
    private function _registerClass()
    {
        try {
            if (!Cache::isCacheValid($this->_api, $this->_class) || $this->_isDevelopment()) {
                try {
                    $this->_parseClass();
                } catch (\Exception $e) {
                    throw new RestException('Error registering class "' . $this->_class . '". ' . $e->getMessage());
                }
            }
        } catch (\Exception $e) {
            throw new RestException('Unable to register class "' . $this->_class . '". ' . $e->getMessage());
        }
    }

    /**
     * Calls the Parser to parse the class and
     * then Compiler to create a compiled cache file of the parsed class.
     */
    private function _parseClass()
    {
        $parser = new Parser();
        $parsedApi = $parser->parseApi($this->_class, $this->_normalize);

        // in development we always write cache
        $writer = new Compiler($this->_api, $this->_normalize);
        $writer->writeCacheFiles($parsedApi);
    }
}