<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Router;

use Webiny\Component\Http\HttpTrait;
use Webiny\Component\Router\Route\RouteCollection;
use Webiny\Component\StdLib\StdLibTrait;
use Webiny\Component\StdLib\StdObject\UrlObject\UrlObject;

/**
 * UrlGenerator can generate urls from the Routes.
 *
 * @package         Webiny\Component\Router
 */
class UrlGenerator
{

    use StdLibTrait, HttpTrait;

    /**
     * Generate a url from a route.
     *
     * @param string $name       Name of the Route.
     * @param array  $parameters List of parameters that need to be replaced within the Route path.
     * @param bool   $absolute   Do you want to get the absolute url or relative. Default is absolute.
     *
     * @return string Generated url.
     * @throws RouterException
     */
    function generate($name, $parameters = [], $absolute = true)
    {
        $route = Router::getRouteCollection()->get($name);
        if ($this->isNull($route)) {
            throw new RouterException('Unknown route "%s".', [$name]);
        }

        $count = 0;
        $unknownParams = [];
        $path = $route->getRealPath();

        // replace provided parameters
        foreach ($parameters as $pk => $pv) {
            $path = str_replace('{' . $pk . '}', $pv, $path, $count);
            if ($count < 1) {
                $unknownParams[$pk] = $pv;
            }
        }

        // replace default parameters
        if (strpos($path, '{') !== false) {
            foreach ($route->getOptions() as $name => $data) {
                if ($data->getAttribute('Default', false)) {
                    $path = str_replace('{' . $name . '}', $data->getAttribute('Default'), $path);
                }
            }
        }

        if (strpos($path, '{') !== false) {
            throw new RouterException('Unable to generate a url for "%s" route. Some parameters are missing: "%s"', [
                    $name,
                    $path
                ]
            );
        }

        /**
         * @var $url UrlObject
         */
        $url = $this->httpRequest()->getCurrentUrl(true)->setPath($path)->setQuery($unknownParams);

        $path = $url->getPath();

        if (!$absolute) {
            $query = $url->getQuery();
            if (count($query) > 0) {
                $query = '?' . http_build_query($query);
            } else {
                $query = '';
            }

            return $path . $query;
        }

        return $url->setPath($path)->val();
    }

}