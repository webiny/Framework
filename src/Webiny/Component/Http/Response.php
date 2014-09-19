<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Http;

use Webiny\Component\Http\Response\CacheControl;
use Webiny\Component\Http\Response\ResponseException;
use Webiny\Component\StdLib\StdObjectTrait;

/**
 * This is the class for building HTTP responses.
 *
 * @package         Webiny\Component\Http\Response
 */
class Response
{
    use StdObjectTrait;

    const PROTOCOL = 'HTTP/1.1';
    const CHARSET = 'UTF-8';
    const CONTENT_TYPE = 'text/html';

    /**
     * @var string Holds the response content that will be rendered on the page.
     */
    private $_content;

    /**
     * @var int Holds the response HTTP status code.
     */
    private $_statusCode;

    /**
     * @var string HTTP response status message;
     */
    private $_statusMessage;

    /**
     * @var string Content type header value.
     */
    private $_contentType;

    /**
     * @var string Holds the header charset information.
     */
    private $_charset;

    /**
     * Holds header information.
     *
     * @var \Webiny\Component\StdLib\StdObject\ArrayObject\ArrayObject
     */
    private $_headers;

    private $_cacheControl;

    /**
     * @var array An array of http status codes by RFC2616 standard or some other if noted by comment.
     */
    public static $httpStatuses = array(
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        // RFC2518
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        // RFC4918
        208 => 'Already Reported',
        // RFC5842
        226 => 'IM Used',
        // RFC3229
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Reserved',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        // RFC-reschke-http-status-308-07
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        // RFC2324
        422 => 'Unprocessable Entity',
        // RFC4918
        423 => 'Locked',
        // RFC4918
        424 => 'Failed Dependency',
        // RFC4918
        425 => 'Reserved for WebDAV advanced collections expired proposal',
        // RFC2817
        426 => 'Upgrade Required',
        // RFC2817
        428 => 'Precondition Required',
        // RFC6585
        429 => 'Too Many Requests',
        // RFC6585
        431 => 'Request Header Fields Too Large',
        // RFC6585
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates (Experimental)',
        // RFC2295
        507 => 'Insufficient Storage',
        // RFC4918
        508 => 'Loop Detected',
        // RFC5842
        510 => 'Not Extended',
        // RFC2774
        511 => 'Network Authentication Required',
        // RFC6585
    );

    /**
     * Constructs the Response object.
     *
     * @param string $content    Content that will be attached to the response.
     * @param int    $statusCode HTTP status code that will be sent back to the user.
     * @param array  $headers    Additional headers that should be attached to the response.
     */
    public function __construct($content = '', $statusCode = 200, $headers = [])
    {
        $this->setContent($content);
        $this->setStatusCode($statusCode);
        $this->_headers = $this->arr($headers);
        $this->_cacheControl = new CacheControl();
        $this->_cacheControl->setAsDontCache();
    }

    /**
     * Static constructor.
     *
     * @param string $content    Content that will be attached to the response.
     * @param int    $statusCode HTTP status code that will be sent back to the user.
     * @param array  $headers    Additional headers that should be attached to the response.
     *
     * @return Response
     */
    public static function create($content = '', $statusCode = 200, $headers = [])
    {
        return new self($content, $statusCode, $headers);
    }

    /**
     * Set response content.
     *
     * @param string $content Response content.
     *
     * @return $this
     * @throws Response\ResponseException
     */
    public function setContent($content)
    {
        if (!is_string($content)) {
            throw new ResponseException('Response content must be a string.');
        }

        $this->_content = $content;

        return $this;
    }

    /**
     * Get response content.
     *
     * @return string
     */
    public function getContent()
    {
        return $this->_content;
    }

    /**
     * Set HTTP response status code. The status code must be a valid HTTP status code, or an exception will be thrown.
     *
     * @param int    $statusCode Http status code.
     * @param string $message    Http status message. If not set we will use the default message by RFC2616.
     *
     * @return $this
     * @throws Response\ResponseException
     */
    public function setStatusCode($statusCode, $message = '')
    {
        if (empty($message)) {
            if (!isset(self::$httpStatuses[$statusCode])) {
                throw new ResponseException('Invalid status code provided: "' . $statusCode . '".');
            } else {
                $this->_statusMessage = self::$httpStatuses[$statusCode];
            }
        } else {
            $this->_statusMessage = $message;
        }

        $this->_statusCode = $statusCode;

        return $this;
    }

    /**
     * Returns current status code.
     *
     * @return int
     */
    public function getStatusCode()
    {
        return $this->_statusCode;
    }

    /**
     * Sets the Content-Type header value.
     *
     * @param string $contentType
     *
     * @return $this
     */
    public function setContentType($contentType)
    {
        $this->_contentType = $contentType;

        $this->setHeader('Content-Type', $contentType . '; charset=' . $this->getCharset());

        return $this;
    }

    /**
     * Returns the currently set content type.
     *
     * @return string
     */
    public function getContentType()
    {
        return (isset($this->_contentType) ? $this->_contentType : self::CONTENT_TYPE);
    }

    /**
     * Set the response charset.
     *
     * @param string $charset Charset name.
     *
     * @return $this
     */
    public function setCharset($charset)
    {
        $this->_charset = $charset;
        $this->setContentType($this->getContentType()); // update charset which is set on content-type

        return $this;
    }

    /**
     * Returns currently set response charset.
     *
     * @return string
     */
    public function getCharset()
    {
        return (isset($this->_charset) ? $this->_charset : self::CHARSET);
    }

    /**
     * Sets or adds a header.
     *
     * @param string $key   Header name.
     * @param string $value Header value.
     *
     * @return $this
     */
    public function setHeader($key, $value)
    {
        $this->_headers->key($key, $value);

        return $this;
    }

    /**
     * Returns an array of current headers.
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->_headers->val();
    }

    /**
     * Sets the response as not modified.
     *
     * @return $this
     */
    public function setAsNotModified()
    {
        $this->setStatusCode(304);
        $this->setContent('');

        // remove headers that MUST NOT be included with 304 Not Modified responses
        $headersToRemove = [
            'Allow',
            'Content-Encoding',
            'Content-Language',
            'Content-Length',
            'Content-MD5',
            'Content-Type',
            'Last-Modified'
        ];
        foreach ($headersToRemove as $header) {
            $this->_headers->removeKey($header);
        }

        return $this;
    }

    /**
     * Get access to cache control headers.
     *
     * @return CacheControl
     */
    public function cacheControl()
    {
        return $this->_cacheControl;
    }

    /**
     * Send the currently defined headers.
     * This also sends the cache control headers.
     *
     * @return $this
     */
    public function sendHeaders()
    {
        // first build headers -> only if they haven't already been sent
        if (!headers_sent()) {
            // status code header
            header(self::PROTOCOL . ' ' . $this->getStatusCode() . ' ' . $this->_statusMessage
            );

            // other headers
            foreach ($this->_headers as $k => $v) {
                header($k . ': ' . $v);
            }

            // cache control headers
            $cacheControlHeaders = $this->_cacheControl->getCacheControl();
            foreach ($cacheControlHeaders as $k => $v) {
                header($k . ': ' . $v);
            }
        }

        return $this;
    }

    /**
     * Sends the content to the browser.
     *
     * @return $this
     */
    public function sendContent()
    {
        echo $this->_content;

        return $this;
    }

    /**
     * Sends both the headers and the content to the browser.
     *
     * @return $this
     */
    public function send()
    {
        $this->sendHeaders()->sendContent();

        // close the request
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        } elseif (strtolower(PHP_SAPI) != 'cli') {
            ob_end_flush();
        }

        return $this;
    }
}