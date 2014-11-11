<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Rest\Response;

use Webiny\Component\Http\HttpTrait;
use Webiny\Component\Http\Response\JsonResponse;
use Webiny\Component\Rest\RestException;
use Webiny\Component\StdLib\StdLibTrait;

/**
 * CallbackResult holds the result returned from the api method.
 *
 * @package         Webiny\Component\Rest\Response
 */
class CallbackResult
{
    use HttpTrait, StdLibTrait;

    /**
     * @var array An array of default messages based on http response status code.
     */
    private static $_defaultMessages = [
        200 => 'OK',
        // Response to a successful GET, PUT, PATCH or DELETE.

        201 => 'Created',
        // Response to a POST that results in a creation.

        204 => 'No Content',
        // Response to a successful request that won't be returning a body (DELETE request).

        304 => 'Not Modified',
        // Used when HTTP caching headers are in play.

        400 => 'Bad Request',
        // The request is malformed, such as if the body does not parse.

        401 => 'Unauthorized',
        // When no or invalid authentication details are provided.

        403 => 'Forbidden',
        // When authentication succeeded but authenticated user doesn't have access level.

        404 => 'Not Found',
        // When a non-existent resource is requested.

        405 => 'Method Not Allowed',
        // When an HTTP method is being requested that isn't allowed.

        410 => 'Gone',
        // Indicates that the resource at this end point is no longer available.

        415 => 'Unsupported Media Type',
        // If incorrect content type was provided as part of the request.

        422 => 'Unprocessable Entity',
        // Used for validation errors.

        429 => 'Too Many Requests'
        // When a request is rejected due to rate limiting

    ];

    /**
     * @var int Default status code.
     */
    private $_statusCode = 200;

    /**
     * @var string Default status code message
     */
    private $_message = 'OK';

    /**
     * @var array List of attached debug headers.
     */
    private $_debugHeaders = [];

    /**
     * @var array Array containing output data.
     */
    private $_outputArray;

    /**
     * @var string string Environment.
     */
    private $_env = 'production';

    /**
     * @var int Number of seconds that defines the Expires cache header.
     */
    private $_expiresIn = 0;


    /**
     * Set response header status code and message.
     *
     * @param int    $statusCode Response status code.
     * @param string $message    Response message.
     *
     * @return $this
     * @throws \Webiny\Component\Rest\RestException
     */
    public function setHeaderResponse($statusCode, $message = '')
    {
        $this->_statusCode = $statusCode;
        $this->_message = $message;
        if ($message == '') {
            $this->_message = self::$_defaultMessages[$statusCode];
        }

        if (empty($this->_message)) {
            throw new RestException('Invalid http response status code: ' . $statusCode);
        }

        return $this;
    }

    /**
     * Adds a general error to the response.
     *
     * @param string $message     Error message.
     * @param string $description Error description.
     * @param string $code        Error code.
     *
     * @return $this
     */
    public function setErrorResponse($message, $description = '', $code = '')
    {
        $this->_outputArray['errorReport']['message'] = $message;

        if (!empty($description)) {
            $this->_outputArray['errorReport']['description'] = $description;
        }

        if (!empty($code)) {
            $this->_outputArray['errorReport']['code'] = $code;
        }

        return $this;
    }

    /**
     * Appends an error entry to the output.
     *
     * @param array $error Error data
     *
     * @return $this
     */
    public function addErrorMessage(array $error)
    {
        $this->_outputArray['errorReport']['errors'][] = $error;

        return $this;
    }

    /**
     * If there are errors, error report array is returned, otherwise false.
     *
     * @return bool|array
     */
    public function getError()
    {
        return empty($this->_outputArray['errorReport']) ? false : $this->_outputArray['errorReport'];
    }

    /**
     * Adds a debug message to the debug output section.
     *
     * @param string|array $message Debug message.
     *
     * @return $this
     */
    public function addDebugMessage($message)
    {
        $this->_outputArray['debug'][] = $message;

        return $this;
    }

    /**
     * Sets the callback content data.
     *
     * @param mixed $data Data that will be set into output 'data' field.
     *
     * @return $this
     */
    public function setData($data)
    {
        $this->_outputArray['data'] = $data;

        return $this;
    }

    /**
     * Returns the data from the service response.
     *
     * @return mixed
     */
    public function getData()
    {
        return $this->_outputArray['data'];
    }

    /**
     * Adds a debug header information.
     *
     * @param string $name      Header name.
     * @param string $value     Header value.
     * @param bool   $ignoreEnv Headers will be added only if we are in development mode, unless you set this to true.
     *
     * @return $this
     */
    public function attachDebugHeader($name, $value, $ignoreEnv = false)
    {

        if ($this->_env != 'development' && !$ignoreEnv) {
            return $this;
        }

        if (strpos($name, 'X-Webiny-Rest') === false) {
            $name = 'X-Webiny-Rest-' . $name;
        }

        $this->_debugHeaders[$name] = $value;

        return $this;
    }

    /**
     * Returns the output in form of an array.
     *
     * @return array
     */
    public function getOutput()
    {
        return $this->_outputArray;
    }

    /**
     * Sets environment flag to development.
     *
     * @return $this
     */
    public function setEnvToDevelopment()
    {
        $this->_env = 'development';

        return $this;
    }

    /**
     * Sets the cache control expiration time.
     *
     * @param int $expiresIn
     *
     * @return $this
     */
    public function setExpiresIn($expiresIn)
    {
        $this->_expiresIn = $expiresIn;

        return $this;
    }

    /**
     * Sends the output to browser.
     */
    public function sendOutput()
    {
        // check environment to see what and how to do the output
        $prettyPrint = false;
        if ($this->_env == 'development') {
            $prettyPrint = true;
            unset($this->_outputArray['debug']);
        }

        // if there is an error, we always dump the content
        if (!empty($this->_outputArray['errorReport'])) {
            unset($this->_outputArray['data']);
        }

        // build response
        if (empty($this->_outputArray)) {
            $response = $this->httpResponse();
        } else {
            $response = new JsonResponse($this->_outputArray, $this->_debugHeaders, $prettyPrint);
        }

        // set proper status code to the response
        $response->setStatusCode($this->_statusCode, $this->_message);

        // check the expires header
        if ($this->_expiresIn > 0) {
            $expiration = $this->dateTime()->add('PT' . $this->_expiresIn . 'S');
            $response->cacheControl()->setAsCache($expiration);
        }

        // send it to the browser
        $response->send();
    }
}