<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Rest\Parser;

use Webiny\Component\Annotations\AnnotationsTrait;
use Webiny\Component\Config\ConfigObject;

/**
 * Method parser class parses the method parameters of a api method.
 *
 * @package         Webiny\Component\Rest\Parser
 */
class MethodParser
{
    use AnnotationsTrait;

    /**
     * @var string Name of the class, where the method is, that we are parsing.
     */
    private $_class;

    /**
     * @var \ReflectionMethod Method that will be parsed.
     */
    private $_method;

    /**
     * @var ConfigObject Class annotations that act as default values for method annotations.
     */
    private $_classDefaults;


    /**
     * Base constructor.
     *
     * @param string            $class  Fully qualified class name.
     * @param \ReflectionMethod $method Method that should be parsed.
     */
    public function __construct($class, \ReflectionMethod $method)
    {
        $this->_class = $class;
        $this->_method = $method;
    }

    /**
     * Parse the method and return an instance of ParsedMethod.
     *
     * @return ParsedMethod
     */
    public function parse()
    {
        $annotations = $this->annotationsFromClass($this->_class);
        $this->_classDefaults = $annotations;

        // get method annotations
        $annotations = $this->annotationsFromMethod($this->_class, $this->_method->name);
        $restAnnotations = $annotations->get('rest', new ConfigObject([]));
        $paramAnnotations = $annotations->get('param', new ConfigObject([]));

        // check if we should ignore this method
        if ($restAnnotations->get('ignore', false) !== false) {
            return false;
        }

        // create api url
        $url = $this->_getUrl();

        // create ApiMethod instance
        $parsedMethod = new ParsedMethod($this->_method->name, $url);

        // method
        $parsedMethod->method = $this->_getMethod($restAnnotations);
        // role
        $parsedMethod->role = $this->_getRole($restAnnotations);
        // cache.ttl
        $parsedMethod->cache = $this->_getCache($restAnnotations);
        // header.cache.expires
        $parsedMethod->header = $this->_getHeader($restAnnotations);
        // default
        $parsedMethod->default = $this->_getDefault($restAnnotations);
        // rateControl.ignore
        $parsedMethod->rateControl = $this->_getRateControl($restAnnotations);

        // parse method parameters
        $parameterParser = new ParameterParser($this->_method->getParameters(), $paramAnnotations);
        $parameters = $parameterParser->parse();
        foreach ($parameters as $p) {
            $parsedMethod->addParameter($p);
        }

        return $parsedMethod;
    }

    /**
     * Generates url for the api based on the class and method name.
     *
     * @return string
     */
    private function _getUrl()
    {
        return PathTransformations::methodNameToUrl($this->_method->name);
    }

    /**
     * Returns the name of the http method for accessing the api.
     *
     * @param ConfigObject $annotations Method annotations.
     *
     * @return string Name of the http method, like post, get, etc.
     */
    private function _getMethod($annotations)
    {
        return strtolower($annotations->get('method', $this->_classDefaults->get('method', 'get')));
    }

    /**
     * If method has defined a access rule, this will return the name of the required role for accessing the method.
     *
     * @param ConfigObject $annotations Method annotations.
     *
     * @return string|boolean Name of the role, or false if there is no access rule defined.
     */
    private function _getRole($annotations)
    {
        return $annotations->get('role', $this->_classDefaults->get('role', false));
    }

    /**
     * Extracts parameters regarding the cache form method annotations.
     *
     * @param ConfigObject $annotations Method annotations.
     *
     * @return array An array containing cache settings.
     */
    private function _getCache($annotations)
    {
        return [
            'ttl' => $annotations->get('cache.ttl', $this->_classDefaults->get('cache.ttl', 0))
        ];
    }

    /**
     * Checks if the method is flagged as "default".
     *
     * @param ConfigObject $annotations Method annotations.
     *
     * @return bool
     */
    private function _getDefault($annotations)
    {
        return $annotations->get('default', false);
    }

    /**
     * Extracts http header information from method annotations.
     *
     * @param ConfigObject $annotations Method annotations.
     *
     * @return array
     */
    private function _getHeader($annotations)
    {
        // headers status code depends on the request method type, unless it's forced on the class or method
        $successStatus = $annotations->get('header.status.success', false);
        if (!$successStatus) {
            $successStatus = $this->_classDefaults->get('header.status.success', false);

            if (!$successStatus) {
                $method = strtolower($annotations->get('method', $this->_classDefaults->get('method', 'get')));
                if ($method == 'post') {
                    $successStatus = 201;
                } else {
                    $successStatus = 200;
                }
            }
        }

        return [
            'cache'  => [
                'expires' => $annotations->get('header.cache.expires',
                                               $this->_classDefaults->get('header.cache.expires', 0
                                               )
                )
            ],
            'status' => [
                'success'      => $successStatus,
                'error'        => $annotations->get('header.status.error',
                                                    $this->_classDefaults->get('header.status.error', 404
                                                    )
                ),
                'errorMessage' => $annotations->get('header.status.errorMessage',
                                                    $this->_classDefaults->get('header.status.errorMessage', ''
                                                    )
                )
            ]
        ];
    }

    private function _getRateControl($annotations)
    {
        return $annotations->get('rateControl', [], true);
    }
}