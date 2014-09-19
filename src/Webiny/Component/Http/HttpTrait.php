<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Http;

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
    public static function httpRequest()
    {
        return Request::getInstance();
    }

    /**
     * Get Cookie instance.
     *
     * @return Cookie
     */
    public static function httpCookie()
    {
        return Cookie::getInstance();
    }

    /**
     * Get Session instance.
     *
     * @return Session
     */
    public static function httpSession()
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
    public static function httpResponse($content = '', $statusCode = 200, $headers = [])
    {
        return new Response($content, $statusCode, $headers);
    }
}