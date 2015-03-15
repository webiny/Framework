<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Router\Route;

use Webiny\Component\Router\RouterException;
use Webiny\Component\StdLib\StdLibTrait;

/**
 * RouteOption holds assigned attributes and their values for given option.
 *
 * @package         Webiny\Component\Router\Route
 */
class RouteOption
{
    use StdLibTrait;

    /**
     * @var string
     */
    private $name = '';

    /**
     * @var \Webiny\Component\StdLib\StdObject\ArrayObject\ArrayObject
     */
    private $attributes;


    /**
     * Base constructor.
     *
     * @param string $name       Name of the route parameter.
     * @param array  $attributes An array of attributes that are going to be attached to the parameter.
     */
    public function __construct($name, array $attributes = [])
    {
        $this->name = $name;
        $this->attributes = $this->arr([]);

        foreach ($attributes as $k => $v) {
            $this->addAttribute($k, $v);
        }
    }

    /**
     * Adds an attribute to the parameter.
     *
     * @param string $name  Name of the attribute.
     * @param string $value Value of the attribute.
     *
     * @return $this
     */
    public function addAttribute($name, $value)
    {
        if ($name == 'Pattern') {
            $value = $this->sanitizePattern($name, $value);
        }
        $this->attributes[$name] = $value;

        return $this;
    }

    /**
     * Returns the attribute value.
     *
     * @param string     $name    Name of the attribute for which you wish to get the value.
     * @param null|mixed $default If attribute is not found, what to return. Default is null.
     *
     * @return string
     */
    public function getAttribute($name, $default = null)
    {
        return $this->attributes->key($name, $default, true);
    }

    /**
     * Checks if current option contains the given attribute.
     *
     * @param string $name Attribute name.
     *
     * @return bool
     */
    public function hasAttribute($name)
    {
        return $this->attributes->keyExists($name);
    }

    /**
     * Returns all the attributes.
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes->val();
    }

    /**
     * Sanitizes the given pattern.
     *
     * @param string $name    Name of the attribute.
     * @param string $pattern Pattern to sanitize.
     *
     * @return string
     * @throws \Webiny\Component\Router\RouterException
     */
    private function sanitizePattern($name, $pattern)
    {
        // make sure value is a string
        if (!$this->isString($pattern)) {
            throw new RouterException('The value of %s.%s option must be a string.', [
                    $this->name,
                    $name
                ]
            );
        }

        // filter out some characters from the start and end of the pattern
        $pattern = $this->str($pattern)->trimLeft('^')->trimRight('$');

        // make sure pattern is not empty
        if ($pattern->length() < 1) {
            throw new RouterException('The route for %s.%s cannot be empty.', [
                    $this->name,
                    $name
                ]
            );
        }

        return $pattern->val();
    }
}