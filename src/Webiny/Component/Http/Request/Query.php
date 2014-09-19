<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Http\Request;

use Webiny\Component\StdLib\StdLibTrait;

/**
 * Query Http component.
 *
 * @package         Webiny\Component\Http
 */
class Query
{
    use StdLibTrait;

    private $_queryBag;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->_queryBag = $this->arr($_GET);
    }

    /**
     * Get the value from GET for the given $key.
     *
     * @param string $key   Key name.
     * @param null   $value Default value that will be returned if the $key is not found.
     *
     * @return string Value under the defined $key.
     */
    public function get($key, $value = null)
    {
        return $this->_queryBag->key($key, $value, true);
    }

    /**
     * Returns a list of all GET values.
     *
     * @return array
     */
    public function getAll()
    {
        return $this->_queryBag->val();
    }

}