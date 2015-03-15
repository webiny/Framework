<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Http\Request;

use Webiny\Component\StdLib\StdLibTrait;

/**
 * Post Http component.
 *
 * @package         Webiny\Component\Http
 */
class Post
{
    use StdLibTrait;

    private $postBag;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->postBag = $this->arr($_POST);
    }

    /**
     * Get the value from POST for the given $key.
     *
     * @param string $key   Key name.
     * @param null   $value Default value that will be returned if the $key is not found.
     *
     * @return string Value under the defined $key.
     */
    public function get($key, $value = null)
    {
        return $this->postBag->key($key, $value, true);
    }

    /**
     * Returns a list of all POST values.
     *
     * @return array
     */
    public function getAll()
    {
        return $this->postBag->val();
    }
}