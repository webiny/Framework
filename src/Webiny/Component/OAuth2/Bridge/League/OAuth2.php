<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\OAuth2\Bridge\League;

use League\OAuth2\Client\Provider\AbstractProvider;
use Webiny\Component\OAuth2\Bridge\OAuth2Abstract;
use Webiny\Component\OAuth2\Bridge\OAuth2Exception;
use Webiny\Component\Http\HttpTrait;
use Webiny\Component\StdLib\StdLibTrait;

/**
 * Bridge for OAuth2 library by The PHP League (http://thephpleague.com/).
 *
 * @package         Webiny\Component\OAuth2\Bridge\OAuth2
 */
class OAuth2 extends OAuth2Abstract
{
    use HttpTrait, StdLibTrait;

    /**
     * @var null|\OAuth2\Client
     */
    private $_provider = null;

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
    function __construct($clientId, $clientSecret, $redirectUri)
    {
        $this->_clientId = $clientId;
        $this->_clientSecret = $clientSecret;
        $this->_redirectUri = $redirectUri;
    }

    /**
     * Requests the access token from the OAuth server.
     * You can call this method only on the OAuth redirect_uri page or else the request will fail.
     *
     * @param string $tokenUrl Url to the page where we can get the access token.
     *
     * @throws \Webiny\Component\OAuth2\Bridge\OAuth2Exception
     * @return string Access token.
     */
    function requestAccessToken($tokenUrl)
    {
        $token = $this->_getProviderInstance()->getAccessToken('AuthorizationCode', [
                                                                          'code' => $this->httpRequest()->query('code', '')
                                                                      ]
        );

        return $token->accessToken;
    }

    /**
     * Get access  token.
     *
     * @return string Access token.
     * @throws OAuth2Exception
     */
    function getAccessToken()
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
    function setAccessToken($accessToken)
    {
        $this->_accessToken = $accessToken;
    }

    /**
     * Set the certificate used by OAuth2 requests.
     *
     * @param string $pathToCertificate Absolute path to the certificate file.
     *
     * @return void
     */
    function setCertificate($pathToCertificate)
    {
        return false;
    }

    /**
     * Returns the path to certificate.
     *
     * @return string Path to certificate.
     */
    function getCertificate()
    {
        return false;
    }

    /**
     * @return null|AbstractProvider
     */
    private function _getProviderInstance()
    {
        if (!is_null($this->_provider)) {
            return $this->_provider;
        }

        $provider = $this->str($this->getServerClassName())->explode('\\')->last();

        $providerName = '\League\OAuth2\Client\Provider\\'.$provider;
        $this->_provider = new $providerName([
                                                 'clientId'     => $this->_clientId,
                                                 'clientSecret' => $this->_clientSecret,
                                                 'redirectUri'  => $this->_redirectUri,
                                                 'scopes'       => $this->getScope(),
                                             ]
        );

        return $this->_provider;
    }

}