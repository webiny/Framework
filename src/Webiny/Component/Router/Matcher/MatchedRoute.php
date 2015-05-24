<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Router\Matcher;

use Webiny\Component\Router\Route\Route;

/**
 * An instance of this class is returned by UrlMatcher when he matches a route.
 *
 * @package         Webiny\Component\Router\Matcher
 */
class MatchedRoute
{
    /**
     * @var Route
     */
    private $route;

    /**
     * @var array Params extracted from the matched route.
     */
    private $params;


    /**
     * @param Route $route  Matched Route instance.
     * @param array $params Params extracted from the matched route.
     */
    public function __construct(Route $route, $params)
    {
        $this->route = $route;
        $this->params = $params;
    }

    /**
     * Get callback of the matched route
     *
     * @return string
     */
    public function getCallback()
    {
        return $this->route->getCallback();
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

    /**
     * Checks if the current matched route has the given tags.
     *
     * @param array $tags List of tags to match.
     * @param bool  $matchAll Match all, or only one of the tags.
     *
     * @return bool
     */
    public function hasTags(array $tags, $matchAll = true)
    {
        $routeTags = $this->getRoute()->getTags();
        $diffCount = count(array_diff($tags, $routeTags));
        $tagsCount = count($tags);

        if ($matchAll) {
            if ($diffCount === 0) {
                return true;
            }
        } else {
            if ($tagsCount > $diffCount) {
                return true;
            }
        }

        return false;
    }


    /**
     * Returns the Route instance.
     *
     * @return Route
     */
    public function getRoute()
    {
        return $this->route;
    }

}