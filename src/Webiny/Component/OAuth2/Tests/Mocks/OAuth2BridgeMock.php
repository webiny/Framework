<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\OAuth2\Tests\Mocks;

use Webiny\Component\OAuth2\Bridge\AbstractOAuth2;

/**
 * A mocked OAuth2 bridge.
 *
 * @package         Webiny\Component\OAuth2\Tests\Mocks
 */
class OAuth2BridgeMock extends AbstractOAuth2
{

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
        $this->accessToken = 'access_token';
        $this->certificate = 'certificate_path';
        $this->accessTokenName = 'test_token';

        $this->setOAuth2Server(ServerMock::class);
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
        return true;
    }

    /**
     * Get access  token.
     *
     * @return string Access token.
     */
    public function getAccessToken()
    {
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
        $this->certificate = $pathToCertificate;
    }

    /**
     * Returns the path to certificate.
     *
     * @return string Path to certificate.
     */
    public function getCertificate()
    {
        return $this->certificate;
    }
}