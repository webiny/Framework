<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @link      http://www.webiny.com/wf-snv for the canonical source repository
 * @copyright Copyright (c) 2009-2013 Webiny LTD. (http://www.webiny.com)
 * @license   http://www.webiny.com/framework/license
 * @package   WebinyFramework
 */

namespace Webiny\Component\StdLib\StdObject\UrlObject;

use Webiny\Component\StdLib\StdObject\ArrayObject\ArrayObject;
use Webiny\Component\StdLib\StdObject\StringObject\StringObject;
use Webiny\Component\StdLib\StdObjectTrait;
use Webiny\Component\StdLib\ValidatorTrait;
use Webiny\Component\StdLib\StdObject\StdObjectAbstract;

/**
 * Url standard object.
 * If you want to extract parameters from a url, or to build/change its parts, this is a class for that.
 *
 * @package         Webiny\Component\StdLib\StdObject\UrlObject
 */
class UrlObject extends StdObjectAbstract
{
    use ValidatorTrait, ManipulatorTrait, StdObjectTrait;

    protected $value;

    private $scheme = false;
    private $host = false;
    private $port = '';
    private $path = '';
    private $query = array();


    /**
     * Constructor.
     * Set standard object value.
     *
     * @param string $value
     *
     * @throws UrlObjectException
     */
    public function __construct($value)
    {
        if($this->isInstanceOf($value, $this)) {
            return $value;
        }

        try {
            $value = $this->str($value)->trim();
            $this->value = $value->val();

            $this->validateUrl();
        } catch (\Exception $e) {
            throw new UrlObjectException(UrlObjectException::MSG_INVALID_URL, [$value]);
        }
    }

    /**
     * Build a UrlObject from array parts.
     *
     * @param ArrayObject|array $parts Url parts, possible keys are: 'scheme', 'host', 'port', 'path' and 'query'
     *
     * @throws UrlObjectException
     * @return UrlObject
     */
    static function buildUrl($parts)
    {
        $parts = new ArrayObject($parts);

        ###################
        ### PARSE PARTS ###
        ###################

        // scheme
        $scheme = $parts->key('scheme', '', true);

        // host
        $host = $parts->key('host', '', true);

        // port
        $port = $parts->key('port', '', true);

        // path
        $path = $parts->key('path', '', true);

        // parse query string
        $query = '';
        if($parts->keyExists('query')) {
            if(self::isString($parts->key('query'))) {
                parse_str($parts->key('query'), $queryData);
            } else {
                $queryData = $parts->key('query');
            }

            if(self::isArray($queryData)) {
                $query = $queryData;
            }
        }


        ###################
        ### BUILD URL   ###
        ###################
        $url = '';

        // scheme
        if($scheme && $scheme != '') {
            $url .= $scheme . '://';
        }

        // host
        if($host && $host != '') {
            $url .= $host;
        }

        // port
        if($port != '') {
            $url .= ':' . $port;
        }

        // path
        if($path != '') {
            $url .= $path;
        }

        // query
        if(self::isArray($query)) {
            $query = http_build_query($query);
            if($query != "") {
                $url .= '?' . $query;
            }
        }

        try {
            return new UrlObject($url);
        } catch (\Exception $e) {
            throw new UrlObjectException($e->getMessage());
        }
    }

    /**
     * Builds url from current url elements.
     *
     * @return $this
     */
    private function rebuildUrl()
    {
        $url = self::buildUrl([
                'scheme' => $this->scheme,
                'host'   => $this->host,
                'port'   => $this->port,
                'path'   => $this->path,
                'query'  => $this->query
            ]
        );
        $this->val($url->val());
        return $this;
    }

    /**
     * Get host name, without trailing slash.
     *
     * @return bool|string Host name without the trailing slash.
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Get scheme (eg. http).
     *
     * @return bool|string Url scheme, or false if it's not set.
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * Get port number.
     *
     * @return bool|int Port number, or false if it's not set.
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Get query params as an array from current object.
     *
     * @return array Array containing query params from the current instance.
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Get the domain name of the current url.
     *
     * @return string|bool Domain name, or false it's not set.
     */
    public function getDomain()
    {
        if($this->getScheme() && $this->getHost()) {
            return $this->getScheme() . '://' . $this->getHost();
        }

        return false;
    }

    /**
     * Get the path from the current url.
     *
     * @param bool $asStringObject Return instance of StringObject
     *
     * @return string|StringObject Path from the current instance.
     */
    public function getPath($asStringObject = false)
    {
        if($asStringObject) {
            return $this->str($this->path);
        }

        return $this->path;
    }

    /**
     * Return, or update, current standard objects value.
     *
     * @param null|string $url
     *
     * @return mixed
     */
    public function val($url = null)
    {
        if($this->isNull($url)) {
            return $this->value;
        }

        $this->value = $url;
        $this->validateUrl();

        return $this;
    }

    /**
     * To string implementation.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->val();
    }

    /**
     * Validates current url and parses data like scheme, host, query, and similar from, it.
     *
     * @throws UrlObjectException
     */
    private function validateUrl()
    {
        $urlData = parse_url($this->val());

        if(!$urlData || !$this->isArray($urlData)) {
            throw new UrlObjectException(UrlObjectException::MSG_INVALID_URL, [$this->val()]);
        }

        // extract parts
        $urlData = $this->arr($urlData);

        // scheme
        $this->scheme = $urlData->key('scheme', '', true);
        // host
        $this->host = $urlData->key('host', '', true);
        // port
        $this->port = $urlData->key('port', '', true);
        // path
        $this->path = $urlData->key('path', '', true);

        // parse query string
        if($urlData->keyExists('query')) {
            parse_str($urlData->key('query'), $queryData);
            if($this->isArray($queryData)) {
                $this->query = $queryData;
            }
        }
    }

    /**
     * Get a string for the given header response code.
     *
     * @param integer $headerCode Header code.
     *
     * @return string
     * @throws UrlObjectException
     */
    private function getHeaderResponseString($headerCode)
    {
        switch ($headerCode) {
            case 100:
                $text = 'Continue';
                break;
            case 101:
                $text = 'Switching Protocols';
                break;
            case 200:
                $text = 'OK';
                break;
            case 201:
                $text = 'Created';
                break;
            case 202:
                $text = 'Accepted';
                break;
            case 203:
                $text = 'Non-Authoritative Information';
                break;
            case 204:
                $text = 'No Content';
                break;
            case 205:
                $text = 'Reset Content';
                break;
            case 206:
                $text = 'Partial Content';
                break;
            case 300:
                $text = 'Multiple Choices';
                break;
            case 301:
                $text = 'Moved Permanently';
                break;
            case 302:
                $text = 'Moved Temporarily';
                break;
            case 303:
                $text = 'See Other';
                break;
            case 304:
                $text = 'Not Modified';
                break;
            case 305:
                $text = 'Use Proxy';
                break;
            case 400:
                $text = 'Bad Request';
                break;
            case 401:
                $text = 'Unauthorized';
                break;
            case 402:
                $text = 'Payment Required';
                break;
            case 403:
                $text = 'Forbidden';
                break;
            case 404:
                $text = 'Not Found';
                break;
            case 405:
                $text = 'Method Not Allowed';
                break;
            case 406:
                $text = 'Not Acceptable';
                break;
            case 407:
                $text = 'Proxy Authentication Required';
                break;
            case 408:
                $text = 'Request Time-out';
                break;
            case 409:
                $text = 'Conflict';
                break;
            case 410:
                $text = 'Gone';
                break;
            case 411:
                $text = 'Length Required';
                break;
            case 412:
                $text = 'Precondition Failed';
                break;
            case 413:
                $text = 'Request Entity Too Large';
                break;
            case 414:
                $text = 'Request-URI Too Large';
                break;
            case 415:
                $text = 'Unsupported Media Type';
                break;
            case 500:
                $text = 'Internal Server Error';
                break;
            case 501:
                $text = 'Not Implemented';
                break;
            case 502:
                $text = 'Bad Gateway';
                break;
            case 503:
                $text = 'Service Unavailable';
                break;
            case 504:
                $text = 'Gateway Time-out';
                break;
            case 505:
                $text = 'HTTP Version not supported';
                break;
            default:
                throw new UrlObjectException(UrlObjectException::MSG_ARG_OUT_OF_RANGE, [$headerCode]);
                break;
        }

        return $text;
    }
}