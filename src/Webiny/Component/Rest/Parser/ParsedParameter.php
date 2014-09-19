<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Rest\Parser;

/**
 * This class holds properties parsed from a method parameter.
 *
 * @package         Webiny\Component\Rest\Parser
 */

class ParsedParameter
{
    /**
     * @var string Parameter name.
     */
    public $name;

    /**
     * @var bool Is the parameter required or not.
     */
    public $required;

    /**
     * @var string Parameter type. (string, integer, etc..)
     */
    public $type;

    /**
     * @var mixed Parameter default value.
     */
    public $default;
}