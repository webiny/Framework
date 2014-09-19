<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\OAuth2;

use Webiny\Component\StdLib\StdLibTrait;

/**
 * This class is used for building OAuth2 requests.
 *
 * @package         Webiny\Component\OAuth2\Bridge
 */
class OAuth2Request
{

    use StdLibTrait;

    /**
     * @var OAuth2Abstract
     */
    private $_oauth2;

    /**
     * @var string Request url.
     */
    private $_url;

    /**
     * @var string Request type.
     */
    private $_requestType = 'GET';

    /**
     * @var array Request parameters.
     */
    private $_params = [];

    /**
     * @var array Request headers.
     */
    private $_headers = [];

    /**
     * @var string Certificate file
     */
    private $_certificateFile = '';


    /**
     * Base constructor.
     *
     * @param OAuth2 $oauth2
     */
    public function __construct(OAuth2 $oauth2)
    {
        $this->_oauth2 = $oauth2;

        $this->_certificateFile = $oauth2->getCertificate();
    }

    /**
     * Set the request destination.
     *
     * @param string $url Url to the destination.
     */
    public function setUrl($url)
    {
        $this->_url = $url;
    }

    /**
     * Get current request destination url.
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->_url;
    }

    /**
     * Set the request type either to POST or GET.
     *
     * @param string $requestType Can be POST or GET
     *
     * @throws OAuth2Exception
     */
    public function setRequestType($requestType)
    {
        $requestType = $this->str($requestType)->caseUpper();

        if (!$requestType->equals('GET') && !$requestType->equals('POST')) {
            throw new OAuth2Exception('Invalid request type provided "' . $requestType->val() . '".
										Possible values are GET and POST.'
            );
        }

        $this->_requestType = $requestType->val();
    }

    /**
     * Get current request type.
     *
     * @return string
     */
    public function getRequestType()
    {
        return $this->_requestType;
    }

    /**
     * Set the request params.
     *
     * @param array $params
     */
    public function setParams(array $params)
    {
        $this->_params = $params;
    }

    /**
     * Get current params.
     *
     * @return array
     */
    public function getParams()
    {
        return $this->_params;
    }

    /**
     * Set the request header params.
     *
     * @param array $headers
     */
    public function setHeaders(array $headers)
    {
        $this->_headers = $headers;
    }

    /**
     * Returns current headers.
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->_headers;
    }

    /**
     * Executes the OAuth2 request.
     *
     * @return array Array containing [result, code, content_type].
     *
     * @throws OAuth2Exception
     */
    public function executeRequest()
    {

        // curl general
        $curl_options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_CUSTOMREQUEST  => $this->_requestType
        ];

        // set to post
        if ($this->_requestType == 'POST') {
            $curl_options[CURLOPT_POST] = true;
        }

        // build url and append query params
        $url = $this->url($this->getUrl());
        $this->_params[$this->_oauth2->getAccessTokenName()] = $this->_oauth2->getAccessToken();
        if (count($this->getParams()) > 0) {
            $url->setQuery($this->getParams());
        }
        $curl_options[CURLOPT_URL] = $url->val();

        // check request headers
        if (count($this->getHeaders()) > 0) {
            $header = [];
            foreach ($this->getHeaders() as $key => $parsed_urlvalue) {
                $header[] = "$key: $parsed_urlvalue";
            }
            $curl_options[CURLOPT_HTTPHEADER] = $header;
        }

        // init curl
        $ch = curl_init();
        curl_setopt_array($ch, $curl_options);

        // https handling
        if ($this->_certificateFile != '') {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_CAINFO, $this->_certificateFile);
        } else {
            // bypass ssl verification
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        }

        // execute the curl request
        $result = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        if ($curl_error = curl_error($ch)) {
            throw new OAuth2Exception($curl_error);
        } else {
            $json_decode = $this->jsonDecode($result, true);
        }
        curl_close($ch);

        return [
            'result'       => (null === $json_decode) ? $result : $json_decode,
            'code'         => $http_code,
            'content_type' => $content_type
        ];
    }
}