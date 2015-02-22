<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Http;

use Webiny\Component\StdLib\StdObject\UrlObject\UrlObject;

/**
 * HttpTrait give you access to Http components such as Request, Server, Session, Cookie, etc.
 *
 * @package         Webiny\Component\Http
 */

trait HttpTrait
{

    /**
     * Get Request component instance.
     *
     * @return Request
     */
    protected static function httpRequest()
    {
        return Request::getInstance();
    }

    /**
     * Get Cookie instance.
     *
     * @return Cookie
     */
    protected static function httpCookie()
    {
        return Cookie::getInstance();
    }

    /**
     * Get Session instance.
     *
     * @return Session
     */
    protected static function httpSession()
    {
        return Session::getInstance();
    }

    /**
     * Creates and returns a new Response instance.
     *
     * @param string $content    Content that will be attached to the response.
     * @param int    $statusCode HTTP status code that will be sent back to the user.
     * @param array  $headers    Additional headers that should be attached to the response.
     *
     * @return Response
     */
    protected static function httpResponse($content = '', $statusCode = 200, $headers = [])
    {
        return new Response($content, $statusCode, $headers);
    }

    /**
     * Redirect the request to the given url.
     *
     * @param string|UrlObject $url
     * @param string|int|array $headers Headers that you wish to send with your request.
     */
    protected static function httpRedirect($url, $headers = null)
    {
        $url = new UrlObject($url);
        $url->goToUrl($headers); // this method dies when it's executed
    }
}