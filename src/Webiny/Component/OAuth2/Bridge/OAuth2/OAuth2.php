<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\OAuth2\Bridge\OAuth2;

use OAuth2\Client;
use Webiny\Component\OAuth2\Bridge\OAuth2Abstract;
use Webiny\Component\OAuth2\Bridge\OAuth2Exception;
use Webiny\Component\Http\HttpTrait;
use Webiny\Component\StdLib\StdLibTrait;

/**
 * Bridge for OAuth2 library by Charron Pierrick (https://github.com/adoy/PHP-OAuth2)
 *
 * @package         Webiny\Component\OAuth2\Bridge\OAuth2
 */
class OAuth2 extends OAuth2Abstract
{

    use HttpTrait, StdLibTrait;

    /**
     * @var null|\OAuth2\Client
     */
    private $_instance = null;

    /**
     * @var string
     */
    private $_pathToCertificate = '';

    /**
     * @var string
     */
    private $_accessToken = '';


    /**
     * Base constructor.
     *
     * @param string $clientId     Client id.
     * @param string $clientSecret Client secret.
     * @param string $redirectUri  Target url where to redirect after authentication.
     */
    public function __construct($clientId, $clientSecret, $redirectUri)
    {
        $this->_clientId = $clientId;
        $this->_clientSecret = $clientSecret;
        $this->_redirectUri = $redirectUri;

        $this->_instance = new Client($clientId, $clientSecret, Client::AUTH_TYPE_URI, null);
    }

    /**
     * Requests the access token from the OAuth server.
     * You can call this method only on the OAuth redirect_uri page or else the request will fail.
     *
     * @param string $tokenUrl Url to the page where we can get the access token.
     *
     * @return string Access token.
     */
    public function requestAccessToken($tokenUrl)
    {

        $params = [
            'code'         => $this->httpRequest()->query('code', ''),
            'redirect_uri' => $this->getRedirectURI()
        ];

        return $this->_instance->getAccessToken($tokenUrl, 'authorization_code', $params);
    }

    /**
     * Get access  token.
     *
     * @throws \Webiny\Component\OAuth2\Bridge\OAuth2Exception
     * @return string Access token.
     */
    public function getAccessToken()
    {
        if ($this->_accessToken == '') {
            throw new OAuth2Exception('Before you can get the access token, you first must request it from the OAuth2 server.'
            );
        }

        return $this->_accessToken;
    }

    /**
     * Set the access token.
     *
     * @param string $accessToken Access token.
     *
     * @return void
     */
    public function setAccessToken($accessToken)
    {
        $this->_instance->setAccessToken($accessToken);
        $this->_accessToken = $accessToken;
    }

    /**
     * Set the certificate used by OAuth2 requests.
     *
     * @param string $pathToCertificate Absolute path to the certificate file.
     *
     * @return void
     */
    public function setCertificate($pathToCertificate)
    {
        $this->_pathToCertificate = $pathToCertificate;
        $this->_instance = new Client($this->_clientId, $this->_clientSecret, Client::AUTH_TYPE_URI, $pathToCertificate
        );
    }

    /**
     * Returns the path to certificate.
     *
     * @return string Path to certificate.
     */
    public function getCertificate()
    {
        return $this->_pathToCertificate;
    }
}