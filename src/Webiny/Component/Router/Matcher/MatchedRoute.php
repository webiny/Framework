<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Router\Matcher;

/**
 * An instance of this class is returned by UrlMatcher when he matches a route.
 *
 * @package         Webiny\Component\Router\Matcher
 */

class MatchedRoute
{
    /**
     * @var string Callback of the matched route.
     */
    private $callback;

    /**
     * @var array Params extracted from the matched route.
     */
    private $params;


    /**
     * @param string $callback Callback of the matched route.
     * @param array  $params   Params extracted from the matched route.
     */
    public function __construct($callback, $params)
    {
        $this->callback = $callback;
        $this->params = $params;
    }

    /**
     * Get callback of the matched route
     *
     * @return string
     */
    public function getCallback()
    {
        return $this->callback;
    }

    /**
     * Get params extracted from the matched route.
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }
}