<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Http\Request;

use Webiny\Component\StdLib\StdLibTrait;

/**
 * Server Http component.
 * This class provide OO methods for accessing $_SERVER properties.
 *
 * @package         Webiny\Component\Http
 */
class Server
{
    use StdLibTrait;

    private $_serverBag;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->_serverBag = $this->arr($_SERVER);
    }

    public function getAll()
    {
        return $this->_serverBag->val();
    }

    /**
     * @param string $key $_SERVER key
     *
     * @return string
     */
    public function get($key)
    {
        return $this->_serverBag->key($key, false, true);
    }

    /**
     * @return string What revision of the CGI specification the server is using; i.e. 'CGI/1.1'.
     */
    public function gatewayInterface()
    {
        return $this->get('GATEWAY_INTERFACE');
    }

    /**
     * @return string The IP address of the server under which the current script is executing.
     */
    public function serverIpAddress()
    {
        return $this->get('SERVER_ADDR');
    }

    /**
     * @return string The name of the server host under which the current script is executing.
     *                    If the script is running on a virtual host, this will be the value defined for that virtual host.
     */
    public function serverName()
    {
        return $this->get('SERVER_NAME');
    }

    /**
     * @return string Server identification string, given in the headers when responding to requests.
     */
    public function serverSoftware()
    {
        return $this->get('SERVER_SOFTWARE');
    }

    /**
     * @return string Name and revision of the information protocol via which the page was requested; i.e. 'HTTP/1.0';
     */
    public function serverProtocol()
    {
        return $this->get('SERVER_PROTOCOL');
    }

    /**
     * @return string Which request method was used to access the page; i.e. 'GET', 'HEAD', 'POST', 'PUT'.
     *                  NOTE: PHP script is terminated after sending headers
     *                        (it means after producing any output without output buffering) if the request method was HEAD.
     */
    public function requestMethod()
    {
        return $this->get('REQUEST_METHOD');
    }

    /**
     * @param bool $float Microsecond precision or not.
     *
     * @return string The timestamp of the start of the request.
     */
    public function requestTime($float = false)
    {
        if ($float) {
            return $this->get('REQUEST_TIME_FLOAT');
        } else {
            return $this->get('REQUEST_TIME');
        }
    }

    /**
     * @return string The query string, if any, via which the page was accessed.
     */
    public function queryString()
    {
        return $this->get('QUERY_STRING');
    }

    /**
     * @return string The document root directory under which the current script is executing,
     *                    as defined in the server's configuration file.
     */
    public function documentRoot()
    {
        return $this->get('DOCUMENT_ROOT');
    }

    /**
     * @return string Contents of the Accept: header from the current request, if there is one.
     */
    public function httpAccept()
    {
        return $this->get('HTTP_ACCEPT');
    }

    /**
     * @return string Contents of the Accept-Charset: header from the current request, if there is one. Example: 'iso-8859-1,*,utf-8'.
     */
    public function httpAcceptCharset()
    {
        return $this->get('HTTP_ACCEPT_CHARSET');
    }

    /**
     * @return string Contents of the Accept-Encoding: header from the current request, if there is one. Example: 'gzip'.
     */
    public function httpAcceptEncoding()
    {
        return $this->get('HTTP_ACCEPT_ENCODING');
    }

    /**
     * @return string Contents of the Accept-Language: header from the current request, if there is one. Example: 'en'.
     */
    public function httpAcceptLanguage()
    {
        return $this->get('HTTP_ACCEPT_LANGUAGE');
    }

    /**
     * @return string Contents of the Connection: header from the current request, if there is one. Example: 'Keep-Alive'.
     */
    public function httpConnection()
    {
        return $this->get('HTTP_CONNECTION');
    }

    /**
     * @return string Contents of the Host: header from the current request, if there is one.
     */
    public function httpHost()
    {
        return $this->get('HTTP_HOST');
    }

    /**
     * @return string The address of the page (if any) which referred the user agent to the current page.
     * This is set by the user agent. Not all user agents will set this, and some provide the ability to modify
     * HTTP_REFERER as a feature. In short, it cannot really be trusted.
     */
    public function httpReferer()
    {
        return $this->get('HTTP_REFERER');
    }

    /**
     * @return string Contents of the User-Agent: header from the current request, if there is one.
     * This is a string denoting the user agent being which is accessing the page.
     * A typical example is: Mozilla/4.5 [en] (X11; U; Linux 2.2.9 i586).
     * Among other things, you can use this value with get_browser() to tailor your page's output
     * to the capabilities of the user agent.
     */
    public function httpUserAgent()
    {
        return $this->get('HTTP_USER_AGENT');
    }

    /**
     * @return string Clients' ip address. This date cannot be trusted if you are behind a reverse proxy. In that case
     * you should first check for Server::httpXForwardedFor.
     */
    public function httpClientIp()
    {
        return $this->get('HTTP_CLIENT_IP');
    }

    /**
     * @return string Check for clients ip behind a reverse proxy.
     */
    public function httpXForwardedFor()
    {
        return $this->get('HTTP_X_FORWARDED_FOR');
    }

    /**
     * @return string Set to a non-empty value if the script was queried through the HTTPS protocol.
     * NOTE: Note that when using ISAPI with IIS, the value will be off if the request was not made through the HTTPS protocol.
     */
    public function https()
    {
        return $this->get('HTTPS');
    }

    /**
     * @return string The IP address from which the user is viewing the current page.
     */
    public function remoteAddress()
    {
        return $this->get('REMOTE_ADDR');
    }

    /**
     * @return string The Host name from which the user is viewing the current page.
     * The reverse dns lookup is based off the REMOTE_ADDR of the user.
     */
    public function remoteHost()
    {
        return $this->get('REMOTE_HOST');
    }

    /**
     * @return string The port being used on the user's machine to communicate with the web server.
     */
    public function remotePort()
    {
        return $this->get('REMOTE_PORT');
    }

    /**
     * @return string The authenticated user if the request is internally redirected.
     */
    public function redirectRemoteUser()
    {
        return $this->get('REDIRECT_REMOTE_USER');
    }

    /**
     * @return string The absolute pathname of the currently executing script.
     * Note: If a script is executed with the CLI, as a relative path,
     * such as file.php or ../file.php, $_SERVER['SCRIPT_FILENAME'] will contain the relative path specified by the user.
     */
    public function scriptFilename()
    {
        return $this->get('SCRIPT_FILENAME');
    }

    /**
     * @return string The value given to the SERVER_ADMIN (for Apache) directive in the web server configuration file.
     * If the script is running on a virtual host, this will be the value defined for that virtual host.
     */
    public function serverAdmin()
    {
        return $this->get('SERVER_ADMIN');
    }

    /**
     * @return string The port on the server machine being used by the web server for communication.
     *                    For default setups, this will be '80'; using SSL, for instance,
     *                    will change this to whatever your defined secure HTTP port is.
     *                    Note: Under the Apache 2, you must set UseCanonicalName = On,
     *                    as well as UseCanonicalPhysicalPort = On in order to get the physical (real) port, otherwise,
     *                    this value can be spoofed and it may or may not return the physical port value.
     *                    It is not safe to rely on this value in security-dependent contexts.
     */
    public function serverPort()
    {
        return $this->get('SERVER_PORT');
    }

    /**
     * @return string String containing the server version and virtual host name which
     * are added to server-generated pages, if enabled.
     */
    public function serverSignature()
    {
        return $this->get('SERVER_SIGNATURE');
    }

    /**
     * @return string Filesystem- (not document root-) based path to the current script,
     * after the server has done any virtual-to-real mapping.
     */
    public function pathTranslated()
    {
        return $this->get('PATH_TRANSLATED');
    }

    /**
     * @return string Contains the current script's path.
     * This is useful for pages which need to point to themselves.
     * The __FILE__ constant contains the full path and filename of the current (i.e. included) file.
     */
    public function scriptName()
    {
        return $this->get('SCRIPT_NAME');
    }

    /**
     * @return string The URI which was given in order to access this page; for instance, '/index.html'.
     */
    public function requestUri()
    {
        return $this->get('REQUEST_URI');
    }

    /**
     * @return string When doing Digest HTTP authentication this variable is set to the 'Authorization'
     * header sent by the client (which you should then use to make the appropriate validation).
     */
    public function phpAuthDigest()
    {
        return $this->get('PHP_AUTH_DIGEST');
    }

    /**
     * @return string When doing HTTP authentication this variable is set to the username provided by the user.
     */
    public function phpAuthUser()
    {
        return $this->get('PHP_AUTH_USER');
    }

    /**
     * @return string When doing HTTP authentication this variable is set to the password provided by the user.
     */
    public function phpAuthPw()
    {
        return $this->get('PHP_AUTH_PW');
    }

    /**
     * @return string When doing HTTP authenticated this variable is set to the authentication type.
     */
    public function authType()
    {
        return $this->get('AUTH_TYPE');
    }

    /**
     * @return string Contains any client-provided pathname information trailing the actual script
     * filename but preceding the query string, if available.
     * For instance, if the current script was accessed via
     * the URL http://www.example.com/php/path_info.php/some/stuff?foo=bar,
     * then $_SERVER['PATH_INFO'] would contain /some/stuff.
     */
    public function pathInfo()
    {
        return $this->get('PATH_INFO');
    }

    /**
     * @return string Original version of 'PATH_INFO' before processed by PHP.
     */
    public function origPathInfo()
    {
        return $this->get('ORIG_PATH_INFO');
    }
}