<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Rest\Response;

use Webiny\Component\Http\HttpTrait;
use Webiny\Component\Rest\Compiler\Cache as CompilerCache; // alias set due to problems with phpunit
use Webiny\Component\Rest\Parser\PathTransformations;
use Webiny\Component\Rest\RestException;
use Webiny\Component\StdLib\StdLibTrait;

/**
 * The request router class processes incoming requests and calls the appropriate callbacks on the registered
 * api classes.
 *
 * @package         Webiny\Component\Rest\Response
 */
class Router
{
    use HttpTrait, StdLibTrait;

    /**
     * Name of the request header that forces the api version.
     */
    const HEADER_VERSION = 'X-Webiny-Rest-Version';

    /**
     * @var array List of supported request methods.
     */
    private static $supportedRequestTypes = [
        'get',
        'post',
        'put',
        'delete',
        'patch'
    ];

    /**
     * @var string Name of the rest api configuration.
     */
    private $api;

    /**
     * @var string Name of the rest api class.
     */
    private $class;

    /**
     * @var string Holds the url upon which we will try to match a service in the registered class.
     */
    private $url;

    /**
     * @var string Holds the HTTP method that will be used to match the service in the registered class.
     */
    private $method;

    /**
     * @var string Should we normalize the url parts, or leave them as they are
     */
    private $normalize;

    /**
     * @var CompilerCache
     */
    private $compilerCache;


    /**
     * Base constructor.
     *
     * @param string        $api           Name of the rest api configuration.
     * @param string        $class         Name of the rest api class.
     * @param bool          $normalize     Should the url parts be normalized or not.
     * @param CompilerCache $compilerCache Current compiler cache instance.
     */
    public function __construct($api, $class, $normalize, CompilerCache $compilerCache)
    {
        $this->api = $api;
        $this->class = $class;
        $this->normalize = $normalize;
        $this->compilerCache = $compilerCache;
    }

    /**
     * Set the url.
     *
     * In case a fixed url string is passed directly into the Rest instance, using this method the url will be passed
     * to the router.
     *
     * @param string $url Url upon which we will try to match a service in the registered class.
     */
    public function setUrl($url)
    {
        $url = $this->url($url)->getPath();
        $this->url = $this->str($url)->trimRight('/')->val() . '/';
    }

    /**
     * Returns the current url path.
     *
     * @return string
     */
    public function getUrl()
    {
        if (empty($this->url)) {
            $url = $this->httpRequest()->getCurrentUrl(true)->getPath();
            // we need to have the trailing slash because of the match inside matchRequest
            // and also when matching the default method, we can do an incorrect match if we don't have the start and end slash
            $this->url = $this->str($url)->trimRight('/')->val() . '/';
        }

        return $this->url;
    }

    /**
     * Force sets the http method.
     *
     * @param string $method Method name.
     *
     * @throws \Webiny\Component\Rest\RestException
     */
    public function setHttpMethod($method)
    {
        $method = strtolower($method);
        if (!in_array($method, self::$supportedRequestTypes)) {
            throw new RestException('The provided HTTP is no supported: "' . $method . '".');
        }

        $this->method = $method;
    }

    /**
     * Get the http method.
     *
     * @return string
     */
    public function getMethod()
    {
        if (empty($this->method)) {
            $this->method = strtolower($this->httpRequest()->getRequestMethod());
        }

        return $this->method;
    }

    /**
     * Process the api rest request and return the CallbackResult object.
     *
     * @return CallbackResult
     */
    public function processRequest()
    {
        // get version cache file
        $version = $this->getVersion();

        // get class data from the compiled cache files
        $classData = $this->compilerCache->getCacheContent($this->api, $this->class, $version);

        // match request
        return $this->matchRequest($classData);
    }

    /**
     * Check if there is a specific version set in the request headers, if not, it returns 'current' version.
     *
     * @return string
     */
    private function getVersion()
    {
        return $this->httpRequest()->header(self::HEADER_VERSION, 'current');
    }

    /**
     * Analyzes the request and tries to match an api method.
     *
     * @param array $classData Class array form compiled cache file.
     *
     * @return CallbackResult
     * @throws RestException
     * @throws \Exception
     */
    private function matchRequest(&$classData)
    {
        if (!is_array($classData)) {
            throw new RestException("Invalid class cache data.");
        }

        // build the request url upon which we will do the matching
        try {
            $url = $this->getUrl();
        } catch (\Exception $e) {
            throw $e;
        }


        // get request method
        $method = $this->getMethod();
        if (!in_array($method, self::$supportedRequestTypes)) {
            throw new RestException('Unsupported request method: "' . $method . '"');
        }

        $callbacks = (empty($classData[$method])) ? [] : $classData[$method];

        // match array
        $matchedMethod = [
            'methodData'        => false,
            'matchedParameters' => false
        ];

        // validate that we have the ending class name in the url
        $classUrl = PathTransformations::classNameToUrl($this->class, $this->normalize);
        if (strpos($url, '/' . $classUrl . '/') !== false) {
            $matchedMethod = $this->matchMethod($callbacks, $url);

            // if method was not matched
            if (!$matchedMethod['methodData']) {
                // if no method was matched, let's try to match a default method
                $matchedMethod = $this->matchDefaultMethod($callbacks, $url, $classUrl);
            }
        }

        $methodData = (isset($matchedMethod['methodData'])) ? $matchedMethod['methodData'] : false;
        $matchedParameters = ($matchedMethod['matchedParameters']) ? $matchedMethod['matchedParameters'] : [];

        $requestBag = new RequestBag();
        $requestBag->setClassData($classData)
                   ->setMethodData($methodData)
                   ->setMethodParameters($matchedParameters)
                   ->setApi($this->api);

        $callback = new Callback($requestBag);

        return $callback->getCallbackResult();
    }

    /**
     * Does the matching of method if method name is present in the url.
     *
     * @param array  $callbacks Available callbacks in the current class.
     * @param string $url       Url upon we will do the match
     *
     * @return array
     */
    private function matchMethod($callbacks, $url)
    {
        // match a callback based on url pattern
        $methodData = false;
        $matchedParameters = false;

        // match method
        foreach ($callbacks as $pattern => $data) {
            if (($matchedParameters = $this->doesPatternMatch($pattern, $data, $url)) !== false) {
                $methodData = $data;
                break;
            } else if ($data['resourceNaming'] === false) {
                $matchedParameters = $this->tryMatchingOptionalParams($pattern, $data, $url);
                if ($matchedParameters) {
                    $methodData = $data;
                    break;
                }
            }
        }

        return [
            'methodData'        => $methodData,
            'matchedParameters' => $matchedParameters
        ];
    }

    /**
     * This is the fallback method that tries to match a default method.
     *
     * @param array  $callbacks Available callbacks in the current class.
     * @param string $url       Url upon we will do the match
     *
     * @return array
     */
    private function matchDefaultMethod($callbacks, $url, $classUrl)
    {
        // match a callback based on url pattern
        $methodData = false;
        $matchedParameters = false;

        // match method
        foreach ($callbacks as $pattern => $data) {
            if ($data['default'] !== false && $data['resourceNaming'] === false) {
                // for default method we need to remove the method name from the pattern
                $methodUrl = $data['method'];
                if ($this->normalize) {
                    $methodUrl = PathTransformations::methodNameToUrl($methodUrl);
                }
                $pattern = $classUrl . '/' . str_replace($methodUrl . '/', '', $pattern);

                if (($matchedParameters = $this->doesPatternMatch($pattern, $data, $url)) !== false) {
                    $methodData = $data;
                    break;
                } else {
                    $methodName = $data['method'];
                    $data['method'] = $classUrl;
                    $matchedParameters = $this->tryMatchingOptionalParams($pattern, $data, $url);
                    $data['method'] = $methodName;
                    if ($matchedParameters) {
                        $methodData = $data;
                        break;
                    }
                }
            }
        }

        return [
            'methodData'        => $methodData,
            'matchedParameters' => $matchedParameters
        ];
    }

    /**
     * Checks if $pattern matches $url.
     *
     * @param string $pattern Pattern that will be used for matching.
     * @param array  $data    An array holding the information about the parameters.
     * @param string $url     Url upon we will try to do the match.
     *
     * @return bool True is returned if $pattern matches $url, otherwise false.
     */
    private function doesPatternMatch($pattern, $data, $url)
    {

        // we need regex only if we need to match some parameters
        if (count($data['params']) <= 0) {
            $endingUrl = substr($url, strrpos($url, $pattern));
            if ($endingUrl == $pattern) {
                return [];
            };
        } else {
            $pattern = str_replace('/', '\/', $pattern);
            preg_match('/' . $pattern . '$/', $url, $matches);

            if (isset($matches[0])) {
                array_shift($matches);

                return $matches;
            }
        }

        return false;
    }

    /**
     * This method adds the default values for missing parameters and tries to do the match again.
     * Note: this works only with standard urls and not with resource naming
     *
     *
     * @param string $pattern Pattern that will be used for matching.
     * @param array  $data    An array holding the information about the parameters.
     * @param string $url     Url upon we will try to do the match.
     *
     * @return bool True is returned if $pattern matches $url, otherwise false.
     */
    private function tryMatchingOptionalParams($pattern, $data, $url)
    {
        // first we check if we have any default params
        $hasDefaultParams = false;
        foreach ($data['params'] as $p) {
            if (!is_null($p['default'])) {
                $hasDefaultParams = true;
            }
        }
        if (!$hasDefaultParams) {
            return false;
        }
        // get parameters that we already have in the url
        $methodUrlName = $data['method'];
        if ($this->normalize) {
            $methodUrlName = PathTransformations::methodNameToUrl($methodUrlName);
        }
        $urlParts = explode('/', $url);
        $numIncludedParams = count($urlParts) - (array_search($methodUrlName, $urlParts) + 2);
        $numAddedParams = 0;
        $requiredParamNum = count($data['params']);
        $loopIndex = 0;
        foreach ($data['params'] as $p) {
            if ($loopIndex >= $numIncludedParams) {
                if (!is_null($p['default'])) {
                    $url .= $p['default'] . '/';
                    $numAddedParams++;
                }
            }
            $loopIndex++;
        }
        if (($numIncludedParams + $numAddedParams) != $requiredParamNum) {
            return false;
        }
        return $this->doesPatternMatch($pattern, $data, $url);
    }
}