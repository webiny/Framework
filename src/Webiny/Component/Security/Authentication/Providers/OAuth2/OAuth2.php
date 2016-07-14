<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Security\Authentication\Providers\OAuth2;

use Webiny\Component\Crypt\CryptTrait;
use Webiny\Component\Http\HttpTrait;
use Webiny\Component\OAuth2\OAuth2Loader;
use Webiny\Component\Security\Authentication\Providers\AuthenticationInterface;
use Webiny\Component\Security\Authentication\Providers\Login;
use Webiny\Component\Security\User\AbstractUser;
use Webiny\Component\Security\Token\Token;
use Webiny\Component\Config\ConfigObject;
use Webiny\Component\StdLib\StdLibTrait;

/**
 * OAuth2 authentication provider.
 *
 * @package         Webiny\Component\Security\Authentication\Providers
 */
class OAuth2 implements AuthenticationInterface
{

    use StdLibTrait, HttpTrait, CryptTrait;

    /**
     * @var null|\Webiny\Component\OAuth2\OAuth2
     */
    private $oauth2Instance = null;
    /**
     * @var array
     */
    private $oauth2Roles = [];

    /**
     * Exit triggers. By default it's set to "die". "exception" is used for unit tests.
     */
    const EXIT_TRIGGER_DIE = 'die';
    const EXIT_TRIGGER_EXCEPTION = 'exception';

    /**
     * @var string
     */
    private $exitTrigger = 'die';


    /**
     * Base constructor.
     *
     * @param string $serverName  Name of the OAuth2 server in the current configuration.
     * @param string|array $roles Roles that will be set for the OAuth2 users.
     *
     * @throws OAuth2Exception
     */
    public function __construct($serverName, $roles)
    {
        try {
            $this->oauth2Instance = OAuth2Loader::getInstance($serverName);
            $this->oauth2Roles = (array)$roles;
        } catch (\Exception $e) {
            throw new OAuth2Exception($e->getMessage());
        }
    }

    /**
     * Set the function which will trigger the end of process.
     *
     * @param string $trigger Name of the trigger function. Possible values are "die" or "exception".
     *
     * @throws OAuth2Exception
     */
    public function setExitTrigger($trigger)
    {
        $validTriggers = [
            'die',
            'exception'
        ];
        if (!in_array($trigger, $validTriggers)) {
            throw new OAuth2Exception(('Invalid exit trigger "' . $trigger . '".'));
        }

        $this->exitTrigger = $trigger;
    }

    /**
     * This method is triggered on the login submit page where user credentials are submitted.
     * On this page the provider should create a new Login object from those credentials, and return the object.
     * This object will be then validated by user providers.
     *
     * @param ConfigObject $config Firewall config
     *
     * @throws OAuth2Exception
     * @return Login
     */
    public function getLoginObject(ConfigObject $config)
    {
        // step1 -> get access token
        $oauth2 = $this->getOAuth2Instance();
        if (!$this->httpRequest()->query('code', false)) {
            $this->httpSession()->delete('oauth_token');

            // append state param to make the request more secured
            $state = $this->createOAuth2State();
            $this->httpSession()->save('oauth_state', $state);
            $oauth2->setState($state);

            $oauth2 = $this->getOAuth2Instance();
            $authUrl = $oauth2->getAuthenticationUrl();

            header('Location: ' . $authUrl);
            $this->triggerExit('Redirecting');
        } else {
            if (!$this->httpSession()->get('oauth_token', false)) {
                $accessToken = $oauth2->requestAccessToken();
                $this->httpSession()->save('oauth_token', $accessToken);
            } else {
                $accessToken = $this->httpSession()->get('oauth_token', false);
            }
        }

        // verify oauth state
        $oauthState = $this->httpRequest()->query('state', '');
        $state = $this->httpSession()->get('oauth_state', 'invalid');
        if ($oauthState != $state) {
            throw new OAuth2Exception('The state parameter from OAuth2 response doesn\'t match the users state parameter.'
            );
        }

        $oauth2->setAccessToken($accessToken);

        if ($this->isArray($accessToken) && isset($accessToken['result']['error'])) {
            $this->httpSession()->delete('oauth_token');

            return false;
        }

        // step2 -> return the login object with auth token
        $login = new Login('', '');
        $login->setAttribute('oauth2_server', $oauth2);
        $login->setAttribute('oauth2_roles', $this->oauth2Roles);

        return $login;
    }


    /**
     * This callback is triggered after we validate the given login data from getLoginObject, and the data IS NOT valid.
     * Use this callback to clear the submit data from the previous request so that you don't get stuck in an
     * infinitive loop between login page and login submit page.
     */
    public function invalidLoginProvidedCallback()
    {
        // we don't need this method for OAuth2
    }

    /**
     * This callback is triggered after we have validated user credentials and have created a user auth token.
     *
     * @param AbstractUser $user
     */
    public function loginSuccessfulCallback(AbstractUser $user)
    {
        // we don't need this method for OAuth2
    }

    /**
     * This callback is triggered when the system has managed to retrieve the user from the stored token (either session)
     * or cookie.
     *
     * @param AbstractUser $user
     * @param Token        $token
     *
     * @return mixed
     */
    public function userAuthorizedByTokenCallback(AbstractUser $user, Token $token)
    {
        // we don't need this method for OAuth2
    }

    /**
     * Logout callback is called when user auth token was deleted.
     */
    public function logoutCallback()
    {
        // we don't need this method for OAuth2
    }


    /**
     * @return array|null|\Webiny\Component\OAuth2\OAuth2
     */
    private function getOAuth2Instance()
    {
        return $this->oauth2Instance;
    }

    private function createOAuth2State()
    {
        return uniqid('wf-');
    }

    /**
     * Triggers the exit process from OAuth2 authentication process.
     *
     * @throws OAuth2Exception
     */
    private function triggerExit($msg)
    {
        switch ($this->exitTrigger) {
            case 'die':
                die($msg);
                break;
            case 'exception':
                throw new OAuth2Exception($msg);
                break;
            default:
                die($msg);
                break;
        }
    }
}