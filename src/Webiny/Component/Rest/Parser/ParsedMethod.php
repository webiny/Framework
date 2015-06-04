<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Rest\Parser;

/**
 * This class contains attributes and settings the MethodParser managed to acquire for a certain api method.
 *
 * @package         Webiny\Component\Rest\Parser
 */

class ParsedMethod
{
    /**
     * @var string Name of the method.
     */
    public $name;

    /**
     * @var string Url pattern, without the root path, defining the api location for this method.
     */
    public $urlPattern;

    /**
     * @var bool Does the method use a resource naming url.
     */
    public $resourceNaming;

    /**
     * @var string Name if the http method for accessing this api method.
     */
    public $method = 'get';

    /**
     * @var array Should the method result be cached by Rest component, and for how long.
     *            If ttl value is set to zero, the content will not be cached.
     */
    public $cache = ['ttl' => 0];

    /**
     * @var bool\string Name of the role the user must have in order to access the api method.
     *                  Roles are defined in the Security component, or you can implement AccessInterface and
     *                  define your own way of processing the access rules.
     */
    public $role = false;

    /**
     * @var bool Is the current method a "default" method for the class and the http method type.
     *           Default methods are accessed by sending a request just to the class name, without the method name.
     *           Note that there can be several default methods, each for one http method type.
     */
    public $default = false;

    /**
     * @var array Header information.
     */
    public $header = [
        'cache'  => [
            'expires' => 0
        ],
        'status' => [
            'success'      => 200,
            'error'        => 404,
            'errorMessage' => ''
        ]
    ];

    /**
     * @var array A list of ParsedParameter instances.
     */
    public $params = [];

    /**
     * @var array List of rate control parameters.
     */
    public $rateControl = [];


    /**
     * Base constructor.
     *
     * @param string $name Name of the api method.
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * @param string $urlPattern Url patten used to match the request with the method.
     * @param bool   $resourceNaming  Does the method use a resource naming url.
     */
    public function setUrlPattern($urlPattern, $resourceNaming)
    {
        $this->urlPattern = $urlPattern;
        $this->resourceNaming = $resourceNaming;
    }

    /**
     * Add a parameter for the method.
     *
     * @param ParsedParameter $p
     */
    public function addParameter(ParsedParameter $p)
    {
        $this->params[$p->name] = $p;
    }
}