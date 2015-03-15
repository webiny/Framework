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
    private $provider = null;

    /**
     * @var string
     */
    private $accessToken = '';


    /**
     * Base constructor.
     *
     * @param string $clientId     Client id.
     * @param string $clientSecret Client secret.
     * @param string $redirectUri  Target url where to redirect after authentication.
     */
    public function __construct($clientId, $clientSecret, $redirectUri)
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->redirectUri = $redirectUri;
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
    public function requestAccessToken($tokenUrl)
    {
        $token = $this->getProviderInstance()->getAccessToken('AuthorizationCode', [
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
    public function getAccessToken()
    {
        if ($this->accessToken == '') {
            throw new OAuth2Exception('Before you can get the access token, you first must request it from the OAuth2 server.'
            );
        }

        return $this->accessToken;
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
        $this->accessToken = $accessToken;
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
        return false;
    }

    /**
     * Returns the path to certificate.
     *
     * @return string Path to certificate.
     */
    public function getCertificate()
    {
        return false;
    }

    /**
     * @return null|AbstractProvider
     */
    private function getProviderInstance()
    {
        if (!is_null($this->provider)) {
            return $this->provider;
        }

        $provider = $this->str($this->getServerClassName())->explode('\\')->last();

        $providerName = '\League\OAuth2\Client\Provider\\'.$provider;
        $this->provider = new $providerName([
                                                 'clientId'     => $this->clientId,
                                                 'clientSecret' => $this->clientSecret,
                                                 'redirectUri'  => $this->redirectUri,
                                                 'scopes'       => $this->getScope(),
                                             ]
        );

        return $this->provider;
    }

}