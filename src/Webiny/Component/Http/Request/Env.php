<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Http\Request;

use Webiny\Component\StdLib\StdLibTrait;

/**
 * Env Http component.
 *
 * @package         Webiny\Component\Http
 */
class Env
{
    use StdLibTrait;

    private $envBag;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->envBag = $this->arr($_ENV);
    }

    /**
     * Get the value from environment variables for the given $key.
     *
     * @param string $key   Key name.
     * @param null   $value Default value that will be returned if the $key is not found.
     *
     * @return string Value under the defined $key.
     */
    public function get($key, $value = null)
    {
        return $this->envBag->key($key, $value, true);
    }

    /**
     * Returns a list of all environment variables.
     *
     * @return array
     */
    public function getAll()
    {
        return $this->envBag->val();
    }
}