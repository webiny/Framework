<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Router\Route;

use Webiny\Component\StdLib\StdLibTrait;

/**
 * Route is an object that defines a route and its options.
 *
 * @package         Webiny\Component\Router\Route
 */
class Route
{
    use StdLibTrait;

    /**
     * @var string
     */
    private $_path = '/';

    /**
     * @var string The raw path defined in config
     */
    private $_realPath = '';

    /**
     * @var string
     */
    private $_callback;

    /**
     * @var array
     */
    private $_options = [];

    /**
     * @var string
     */
    private $_host = '';

    /**
     * @var array
     */
    private $_schemes = [];

    /**
     * @var array
     */
    private $_methods = [];

    /**
     * @var null|CompiledRoute
     */
    private $_compiledRoute = null;


    /**
     * Base constructor.
     *
     * @param string       $path     Path with parameter names that identifies the route.
     * @param string|array $callback Attached callback for this route.
     * @param array        $options  List of options, mostly for parameters. Common option keys are 'pattern' and 'default'.
     *                               Pattern defines the regular expression for the defined parameter. Default sets the default value for the parameter
     *                               if it's not matched withing the route.
     * @param string       $host     Fully qualified host name that will be added as a filer for matching the url.
     * @param array        $schemes  An array of supported schemas that the url must match.
     * @param array        $methods  An array of supported methods that the url must match.
     */
    public function __construct($path, $callback, $options = [], $host = '', $schemes = [], $methods = [])
    {
        $this->setPath($path);
        $this->setCallback($callback);
        $this->setOptions($options);
        $this->setHost($host);
        $this->setSchemes($schemes);
        $this->setMethods($methods);
    }

    /**
     * Sets the route path.
     *
     * @param string $path Route path.
     *
     * @return $this
     */
    public function setPath($path)
    {
        $this->_path = '';
        $this->_realPath = $path;

        if(!empty($path)) {
            $this->_path .= $this->str($path)->trim()->trimLeft('/')->trimRight('/')->val();
        }

        return $this;
    }

    /**
     * Get the route path.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->_path;
    }

    /**
     * Get the real path.
     *
     * @return string
     */
    public function getRealPath()
    {
        return $this->_realPath;
    }

    /**
     * Set the route callback.
     *
     * @param string|array $callback Callback that will be attached to this route.
     *                               Note that this callback can be overwritten during the routing process.
     *
     * @return $this
     */
    public function setCallback($callback)
    {
        $this->_callback = $callback;

        return $this;
    }

    /**
     * Get the attached callback name.
     *
     * @return string|array
     */
    public function getCallback()
    {
        return $this->_callback;
    }

    /**
     * Set route options.
     *
     * @param array $options An array of options. Each option must have a name and a list of attributes and their values.
     *                       Common attributes are 'prefix' and 'default'.
     *
     * @return $this
     */
    public function setOptions(array $options)
    {
        $this->_options = [];

        foreach ($options as $k => $v) {
            $this->addOption($k, (array)$v);
        }

        return $this;
    }

    /**
     * Adds a single route option.
     *
     * @param string $name       Name of the parameter to which the option should be attached.
     * @param array  $attributes An array of options.
     *
     * @return $this
     */
    public function addOption($name, array $attributes)
    {
        $this->_options[$name] = new RouteOption($name, $attributes);

        return $this;
    }

    /**
     * Checks if route has options attached to the given parameter.
     *
     * @param string $name Name of the route parameter.
     *
     * @return bool
     */
    public function hasOption($name)
    {
        return array_key_exists($name, $this->_options);
    }

    /**
     * Returns route options.
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->_options;
    }

    /**
     * Sets the host name to the route.
     * When defining a host name, the route must first match the host in order that it could match the route path.
     *
     * @param string $host Host name.
     *
     * @return $this
     */
    public function setHost($host)
    {
        $this->_host = $host;

        return $this;
    }

    /**
     * Get current host name.
     * Example host name: webiny.com | www.webiny.com
     *
     * @return string
     */
    public function getHost()
    {
        return $this->_host;
    }

    /**
     * Match only requests that are within this set of schemes.
     * Example scheme: http | https
     *
     * @param array|string $schemes Scheme(s) to match.
     *
     * @return $this
     */
    public function setSchemes($schemes)
    {
        $this->_schemes = array_map('strtolower', (array)$schemes);

        return $this;
    }

    /**
     * Get the current defined schemes.
     *
     * @return array
     */
    public function getSchemes()
    {
        return $this->_schemes;
    }

    /**
     * Match only requests that are within this set of methods.
     * Example methods: POST | GET
     *
     * @param array|string $methods Method(s) to match.
     *
     * @return $this
     */
    public function setMethods($methods)
    {
        $this->_methods = array_map('strtoupper', $methods);

        return $this;
    }

    /**
     * Get the current defined methods.
     *
     * @return array
     */
    public function getMethods()
    {
        return $this->_methods;
    }

    /**
     * Compiles the route object and returns an instance of CompiledRoute.
     *
     * @return CompiledRoute
     */
    public function compile()
    {
        if($this->isNull($this->_compiledRoute)) {
            $this->_compiledRoute = RouteCompiler::compile($this);
        }

        return $this->_compiledRoute;
    }
}