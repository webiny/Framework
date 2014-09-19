<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Http\Request;

use Webiny\Component\StdLib\StdLibTrait;

/**
 * Request headers.
 *
 * @package         Webiny\Component\Http\Request
 */
class Headers
{
    use StdLibTrait;

    private $_headerBag;

    /**
     * Constructor.
     */
    public function __construct()
    {
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
        } else {
            $headers = $this->_getAllHeaders();
        }

        $this->_headerBag = $this->arr($headers);
    }

    /**
     * Get the value from header variables for the given $key.
     *
     * @param string $key   Key name.
     * @param null   $value Default value that will be returned if the $key is not found.
     *
     * @return string Value under the defined $key.
     */
    public function get($key, $value = null)
    {
        return $this->_headerBag->key($key, $value, true);
    }

    /**
     * Returns a list of all header variables.
     *
     * @return array
     */
    public function getAll()
    {
        return $this->_headerBag->val();
    }

    /**
     * Returns a list of header information.
     *
     * @return array
     */
    private function _getAllHeaders()
    {
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
                $headers[$name] = $value;
            } else {
                if ($name == "CONTENT_TYPE") {
                    $headers["Content-Type"] = $value;
                } else {
                    if ($name == "CONTENT_LENGTH") {
                        $headers["Content-Length"] = $value;
                    }
                }
            }
        }

        return $headers;
    }
}