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
    private $class;

    /**
     * @var \ReflectionMethod Method that will be parsed.
     */
    private $method;

    /**
     * @var ConfigObject Class annotations that act as default values for method annotations.
     */
    private $classDefaults;

    /**
     * @var bool Should the class name and the method name be normalized.
     */
    private $normalize;


    /**
     * Base constructor.
     *
     * @param string            $class     Fully qualified class name.
     * @param \ReflectionMethod $method    Method that should be parsed.
     * @param bool              $normalize Should the class name and the method name be normalized.
     */
    public function __construct($class, \ReflectionMethod $method, $normalize)
    {
        $this->class = $class;
        $this->method = $method;
        $this->normalize = $normalize;
    }

    /**
     * Parse the method and return an instance of ParsedMethod.
     *
     * @return ParsedMethod
     */
    public function parse()
    {
        $annotations = $this->annotationsFromClass($this->class);
        $this->classDefaults = $annotations->get('rest', new ConfigObject([]));

        // get method annotations
        $annotations = $this->annotationsFromMethod($this->class, $this->method->name);
        $restAnnotations = $annotations->get('rest', new ConfigObject([]));
        $paramAnnotations = $annotations->get('param', new ConfigObject([]));

        // check if we should ignore this method
        if ($restAnnotations->get('ignore', false) !== false) {
            return false;
        }

        // create api url
        $url = $this->getUrl();

        // create ApiMethod instance
        $parsedMethod = new ParsedMethod($this->method->name, $url);

        // method
        $parsedMethod->method = $this->getMethod($restAnnotations);
        // role
        $parsedMethod->role = $this->getRole($restAnnotations);
        // cache.ttl
        $parsedMethod->cache = $this->getCache($restAnnotations);
        // header.cache.expires
        $parsedMethod->header = $this->getHeader($restAnnotations);
        // default
        $parsedMethod->default = $this->getDefault($restAnnotations);
        // rateControl.ignore
        $parsedMethod->rateControl = $this->getRateControl($restAnnotations);

        // parse method parameters
        $parameterParser = new ParameterParser($this->method->getParameters(), $paramAnnotations);
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
    private function getUrl()
    {
        if($this->normalize){
            return PathTransformations::methodNameToUrl($this->method->name);
        }

        return $this->method->name;
    }

    /**
     * Returns the name of the http method for accessing the api.
     *
     * @param ConfigObject $annotations Method annotations.
     *
     * @return string Name of the http method, like post, get, etc.
     */
    private function getMethod(ConfigObject $annotations)
    {
        return strtolower($annotations->get('method', $this->classDefaults->get('method', 'get')));
    }

    /**
     * If method has defined a access rule, this will return the name of the required role for accessing the method.
     *
     * @param ConfigObject $annotations Method annotations.
     *
     * @return string|boolean Name of the role, or false if there is no access rule defined.
     */
    private function getRole(ConfigObject $annotations)
    {
        return $annotations->get('role', $this->classDefaults->get('role', false));
    }

    /**
     * Extracts parameters regarding the cache form method annotations.
     *
     * @param ConfigObject $annotations Method annotations.
     *
     * @return array An array containing cache settings.
     */
    private function getCache(ConfigObject $annotations)
    {
        return [
            'ttl' => $annotations->get('cache.ttl', $this->classDefaults->get('cache.ttl', 0))
        ];
    }

    /**
     * Checks if the method is flagged as "default".
     *
     * @param ConfigObject $annotations Method annotations.
     *
     * @return bool
     */
    private function getDefault(ConfigObject $annotations)
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
    private function getHeader(ConfigObject $annotations)
    {
        // headers status code depends on the request method type, unless it's forced on the class or method
        $successStatus = $annotations->get('header.status.success', false);
        if (!$successStatus) {
            $successStatus = $this->classDefaults->get('header.status.success', false);

            if (!$successStatus) {
                $method = strtolower($annotations->get('method', $this->classDefaults->get('method', 'get')));
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
                                               $this->classDefaults->get('header.cache.expires', 0
                                               )
                )
            ],
            'status' => [
                'success'      => $successStatus,
                'error'        => $annotations->get('header.status.error',
                                                    $this->classDefaults->get('header.status.error', 404
                                                    )
                ),
                'errorMessage' => $annotations->get('header.status.errorMessage',
                                                    $this->classDefaults->get('header.status.errorMessage', ''
                                                    )
                )
            ]
        ];
    }

    /**
     * Extracts the rate control information from the method annotations.
     *
     * @param ConfigObject $annotations
     *
     * @return array
     */
    private function getRateControl(ConfigObject $annotations)
    {
        return $annotations->get('rateControl', [], true);
    }
}