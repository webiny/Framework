<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Security\Authentication\Providers\Http;

use Webiny\Component\Config\ConfigObject;
use Webiny\Component\Http\HttpTrait;
use Webiny\Component\Security\Authentication\Providers\AuthenticationInterface;
use Webiny\Component\Security\Authentication\Providers\Login;
use Webiny\Component\Security\Token\Token;
use Webiny\Component\Security\User\UserAbstract;

/**
 * Http authentication
 *
 * @package         Webiny\Component\Security\Authentication\Http
 */
class Http implements AuthenticationInterface
{

    use HttpTrait;

    /**
     * Constants for PHP_AUTH variable names.
     */
    const USERNAME = 'PHP_AUTH_USER';
    const PASSWORD = 'PHP_AUTH_PW';
    const DIGEST = 'PHP_AUTH_DIGEST';

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
     * This method is triggered on the login submit page where user credentials are submitted.
     * On this page the provider should create a new Login object from those credentials, and return the object.
     * This object will be then validated my user providers.
     *
     * @param ConfigObject $config Firewall config
     *
     * @return Login
     */
    public function getLoginObject(ConfigObject $config)
    {
        if (!$this->httpSession()->get('username', false)) {
            $this->triggerLogin($config);
        }

        $username = $this->httpSession()->get('username', '');
        $password = $this->httpSession()->get('password', '');

        return new Login($username, $password, false);
    }

    /**
     * Set the function which will trigger the end of process.
     *
     * @param string $trigger Name of the trigger function. Possible values are "die" or "exception".
     *
     * @throws HttpException
     */
    public function setExitTrigger($trigger)
    {
        $validTriggers = [
            'die',
            'exception'
        ];
        if (!in_array($trigger, $validTriggers)) {
            throw new HttpException(('Invalid exit trigger "' . $trigger . '".'));
        }

        $this->exitTrigger = $trigger;
    }

    /**
     * This method is triggered when the user opens the login page.
     * On this page you must ask the user to provide you his credentials which should then be passed to the login submit page.
     *
     * @param ConfigObject $config Firewall config
     *
     * @return mixed
     */
    public function triggerLogin($config)
    {
        $headers = [
            'WWW-Authenticate: Basic realm="' . $config->RealmName . '"',
            'HTTP/1.0 401 Unauthorized'
        ];

        foreach ($headers as $h) {
            header($h);
        }

        if ($this->httpSession()->get('login_retry') == 'true') {
            $this->httpSession()->delete('login_retry');
            $this->triggerExit();
        }

        // once we get the username and password, we store them into the session and redirect to login submit path
        if ($this->httpRequest()->server()->get(self::USERNAME, '') != '' && $this->httpSession()->get('logout', 'false'
            ) != 'true'
        ) {
            // php Basic HTTP auth
            $username = $this->httpRequest()->server()->get(self::USERNAME);
            $password = $this->httpRequest()->server()->get(self::PASSWORD);
        } else {
            $this->httpSession()->delete('logout');
            $this->triggerExit();
        }

        $this->httpSession()->save('username', $username);
        $this->httpSession()->save('password', $password);

        $this->httpRedirect($this->httpRequest()->getCurrentUrl());
    }

    /**
     * This callback is triggered after we validate the given login data, and the data is not valid.
     * Use this callback to clear the submit data from the previous request so that you don't get stuck in an
     * infinitive loop between login and login submit page.
     */
    public function invalidLoginProvidedCallback()
    {
        $this->httpSession()->delete('username');
        $this->httpSession()->delete('password');
        $this->httpSession()->save('login_retry', 'true');
    }

    /**
     * This callback is triggered after we have validated user credentials.
     *
     * @param UserAbstract $user
     */
    public function loginSuccessfulCallback(UserAbstract $user)
    {
        // nothing to do
    }

    /**
     * This callback is triggered when the system has managed to retrieve the user from the stored token (either session)
     * or cookie.
     *
     * @param UserAbstract $user
     * @param Token        $token
     *
     * @return mixed
     */
    public function userAuthorizedByTokenCallback(UserAbstract $user, Token $token)
    {
        // nothing to do
    }

    /**
     * Logout callback is called when user auth token was deleted.
     */
    public function logoutCallback()
    {
        $this->invalidLoginProvidedCallback();
        $this->httpSession()->delete('login_retry');
        $this->httpSession()->save('logout', 'true');
    }

    /**
     * Triggers the exit process from Http authentication process.
     * This method is used so we can mock the behaviour of this provider, for unit tests.
     *
     * @throws HttpException
     */
    private function triggerExit()
    {
        $msg = 'You are not authenticated';

        switch ($this->exitTrigger) {
            case 'die':
                die($msg);
                break;
            case 'exception':
                throw new HttpException($msg);
                break;
            default:
                die($msg);
                break;
        }
    }
}