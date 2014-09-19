<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\TemplateEngine;

use Webiny\Component\StdLib\StdLibTrait;

/**
 * Template engine plugin class.
 * This class is used by template engines to register plugins.
 * Based on the template engine a plugin can be a modifier, a tag, a filter or something else.
 *
 * @package         Webiny\Component\TemplateEngine
 */
class Plugin
{
    use StdLibTrait;

    /**
     * @var string
     */
    private $_name;

    /**
     * @var string
     */
    private $_type;

    /**
     * @var callable|string
     */
    private $_callbackFunction;

    /**
     * @var array
     */
    private $_params;

    /**
     * @var \Webiny\Component\StdLib\StdObject\ArrayObject\ArrayObject
     */
    private $_attributes;


    /**
     * Base constructor.
     *
     * @param string          $name                            Plugin Name of the plugin.
     * @param string          $type                            Plugin type. The type value depends on the current template engine driver.
     *                                                         They usually have values like "function", "tag", "modifier"..etc.
     *                                                         Basically they define the type of the plugin.
     * @param \Closure|string $callbackFunction                Callback function that holds the plugin logic.
     * @param array           $params                          Optional parameters that can be passed to the plugin.
     */
    public function __construct($name, $type, $callbackFunction, $params = [])
    {
        $this->_name = $name;
        $this->_type = $type;
        $this->_callbackFunction = $callbackFunction;
        $this->_params = $params;
        $this->_attributes = $this->arr([]);
    }

    /**
     * Sets an optional attribute to the plugin.
     *
     * @param string $key   Attribute key.
     * @param mixed  $value Attribute value.
     */
    public function setAttribute($key, $value)
    {
        $this->_attributes->key($key, $value);
    }

    /**
     * Return the attribute value under the defined $key.
     *
     * @param string $key          Attribute key.
     * @param mixed  $defaultValue Default value that the method should return if $key is not found among the attributes.
     *
     * @return mixed
     */
    public function getAttribute($key, $defaultValue = false)
    {
        return $this->_attributes->key($key, $defaultValue, true);
    }

    /**
     * Get plugin name.
     *
     * @return string Plugin name.
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Get plugin type.
     *
     * @return string Plugin type.
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Get the callable function.
     *
     * @return callable|string Plugin callable function.
     */
    public function getCallbackFunction()
    {
        return $this->_callbackFunction;
    }

    /**
     * Get plugin parameters.
     *
     * @return array Plugin params.
     */
    public function getParams()
    {
        return $this->_params;
    }
}