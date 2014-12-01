<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Security\Authentication;

use Webiny\Component\Config\ConfigObject;
use Webiny\Component\EventManager\EventManagerTrait;
use Webiny\Component\Http\HttpTrait;
use Webiny\Component\Security\Authentication\Providers\AuthenticationInterface;
use Webiny\Component\Security\Authentication\Providers\Login;
use Webiny\Component\Security\Authorization\AccessControl;
use Webiny\Component\Security\Encoder\Encoder;
use Webiny\Component\Security\Role\RoleHierarchy;
use Webiny\Component\Security\Security;
use Webiny\Component\Security\SecurityEvent;
use Webiny\Component\Security\User\AnonymousUser;
use Webiny\Component\Security\User\Exceptions\UserNotFoundException;
use Webiny\Component\Security\User\Providers\Memory;
use Webiny\Component\Security\Token\Token;
use Webiny\Component\Security\User\UserAbstract;
use Webiny\Component\StdLib\Exception\Exception;
use Webiny\Component\StdLib\FactoryLoaderTrait;
use Webiny\Component\StdLib\SingletonTrait;
use Webiny\Component\StdLib\StdLibTrait;

/**
 * This is the main class for authentication layer.
 * The firewall class check if users is authenticated and holds the methods for authentication.
 *
 * @package         Webiny\Component\Security\Authentication
 */
class Firewall
{

    use HttpTrait, StdLibTrait, FactoryLoaderTrait, EventManagerTrait;

    /**
     * @var \Webiny\Component\Config\ConfigObject
     */
    private $_config;

    /**
     * @var array An array of user provider instances.
     */
    private $_userProviders = [];

    /**
     * @var string Name of the current firewall.
     */
    private $_firewallKey;

    /**
     * @var \Webiny\Component\Security\Encoder\Encoder
     */
    private $_encoder;

    /**
     * @var Token
     */
    private $_token;

    /**
     * @var bool|UserAbstract
     */
    private $_user = false;

    /**
     * @var bool Has user already been authenticated.
     */
    private $_userAuthenticated = false;

    /**
     * @var AuthenticationInterface
     */
    private $_authProvider;

    /**
     * @var \Webiny\Component\Config\ConfigObject
     */
    private $_authProviderConfig;

    /**
     * @var RoleHierarchy
     */
    private $_roleHierarchy;

    /**
     * @var AccessControl
     */
    private $_accessControl;

    /**
     * Constructor.
     *
     * @param string       $firewallKey    Name of the current firewall.
     * @param ConfigObject $firewallConfig Firewall config.
     * @param array        $userProviders  Array of user providers for this firewall.
     * @param Encoder      $encoder        Instance of encoder for this firewall.
     */
    public function __construct($firewallKey, ConfigObject $firewallConfig, array $userProviders, Encoder $encoder)
    {
        $this->_firewallKey = $firewallKey;
        $this->_config = $firewallConfig;
        $this->_userProviders = $userProviders;
        $this->_encoder = $encoder;

        $this->_initToken();
    }

    /**
     * Call this method on your login submit page, it will trigger the authentication provider and validate the provided
     * credentials.
     *
     * @param string $authProvider Name of the auth provider you wish to use to process the login.
     *                             If you don't set it, the first registered provider will be used.
     *
     * @return bool True if login is valid, false if login has failed.
     * @throws FirewallException
     */
    public function processLogin($authProvider = '')
    {
        try {
            // if we are on login page, first try to get the instance of Login object from current auth provider
            $login = $this->_getAuthProvider($authProvider)->getLoginObject($this->getConfig());
            if (!$this->isInstanceOf($login, 'Webiny\Component\Security\Authentication\Providers\Login')) {
                throw new FirewallException('Authentication provider method getLoginObject() must return an instance of
														"Webiny\Component\Security\Authentication\Providers\Login".'
                );
            }
            $login->setAuthProviderName($authProvider);
        } catch (\Exception $e) {
            throw new FirewallException($e->getMessage());
        }

        // forward the login object to user providers and validate the credentials
        if (!($this->_user = $this->_authenticate($login))) { // login failed
            $this->_getAuthProvider($authProvider)->invalidLoginProvidedCallback();
            $this->eventManager()->fire(SecurityEvent::LOGIN_INVALID, new SecurityEvent(new AnonymousUser()));

            return false;
        } else {
            $this->_getAuthProvider($authProvider)->loginSuccessfulCallback($this->_user);
            $this->eventManager()->fire(SecurityEvent::LOGIN_VALID, new SecurityEvent($this->_user));
            $this->_setUserRoles();
            $this->_userAuthenticated = true;

            return true;
        }
    }

    /**
     * This method deletes user auth token and calls the logoutCallback on current login provider.
     * After that, it replaces the current user instance with an instance of AnonymousUser and redirects the request to
     * the logout.target.
     */
    public function processLogout()
    {
        $this->getToken()->deleteUserToken();
        if ($this->getUser()->isAuthenticated()) {
            $this->_getAuthProvider($this->_user->getAuthProviderName())->logoutCallback();
        }
        $this->_user = new AnonymousUser();
        $this->_userAuthenticated = false;

        $this->eventManager()->fire(SecurityEvent::LOGOUT);

        return true;
    }

    /**
     * Tries to retrieve the user from current token.
     * If the token does not exist, AnonymousUser is returned.
     *
     * @throws FirewallException
     * @return bool|\Webiny\Component\Security\User\UserAbstract
     */
    public function getUser()
    {
        if ($this->_userAuthenticated) {
            return $this->_user;
        }

        try {
            // get token
            $this->_user = new AnonymousUser();
            $tokenData = $this->getToken()->getUserFromToken();

            if (!$tokenData) {
                $this->eventManager()->fire(SecurityEvent::NOT_AUTHENTICATED, new SecurityEvent($this->_user));

                $this->_userAuthenticated = false;

                return $this->_user;
            } else {
                $this->_user->populate($tokenData->getUsername(), '', $tokenData->getRoles(), true);
                $this->_user->setAuthProviderName($tokenData->getAuthProviderName());
                $this->_setUserRoles();

                $this->_userAuthenticated = true;

                return $this->_user;
            }
        } catch (\Exception $e) {
            $this->_userAuthenticated = true;
            throw new FirewallException($e->getMessage());
        }
    }

    /**
     * Checks if current user has access to current area based by access rules.
     *
     * @return bool
     */
    public function isUserAllowedAccess()
    {
        if (!is_object($this->_accessControl)) {
            $this->_accessControl = new AccessControl($this->_user, $this->_config->get('AccessControl', false));
        }

        $isAccessAllowed = $this->_accessControl->isUserAllowedAccess();
        if (!$isAccessAllowed) {
            $this->eventManager()->fire(SecurityEvent::ROLE_INVALID, new SecurityEvent($this->_user));
        }

        return $isAccessAllowed;
    }

    /**
     * Get realm name.
     *
     * @return string Realm name.
     */
    public function getRealmName()
    {
        return $this->_config->RealmName;
    }

    /**
     * Check if anonymous access is allowed or not.
     * If anonymous access is not defined in the config, by default it will be set to false.
     *
     * @return bool Is anonymous access allowed or not.
     */
    public function getAnonymousAccess()
    {
        return $this->_config->get('Anonymous', false);
    }

    /**
     * Get config for current firewall.
     *
     * @return ConfigObject
     */
    public function getConfig()
    {
        return $this->_config;
    }

    /**
     * Get the current token.
     *
     * @return Token
     */
    public function getToken()
    {
        return $this->_token;
    }

    /**
     * Returns the name of the current firewall. Don't mistake it for realm name.
     * @return string
     */
    public function getFirewallKey()
    {
        return $this->_firewallKey;
    }

    /**
     * Create a hash for the given password.
     *
     * @param string $password
     *
     * @return string Password hash.
     */
    public function createPasswordHash($password){
        return $this->_encoder->createPasswordHash($password);
    }

    /**
     * Verify if the $password matches the $hash.
     *
     * @param string $password
     * @param string $hash
     *
     * @return bool True if $password matches $hash. Otherwise false is returned.
     */
    public function verifyPasswordHash($password, $hash){
        return $this->_encoder->verifyPasswordHash($password, $hash);
    }

    /**
     * Returns the config of current auth provider based on current url.
     *
     * @param string $authProvider Name of the auth provider you wish to use to process the login.
     *                             If you don't set it, the first registered provider will be used.
     *
     * @throws FirewallException
     * @return ConfigObject
     */
    private function _getAuthProviderConfig($authProvider)
    {
        // have we already fetched the auth config
        if ($this->_authProviderConfig) {
            return $this->_authProviderConfig;
        }

        if ($authProvider == '') {
            // get the first config
            $providers = $this->getConfig()->get('AuthenticationProviders', []);
            $this->_authProviderConfig = Security::getConfig()->get('AuthenticationProviders.' . $providers[0], false);
        } else {
            $this->_authProviderConfig = Security::getConfig()->get('AuthenticationProviders.' . $authProvider, false);
        }

        if (!$this->_authProviderConfig || !$this->_authProviderConfig->get('Driver', false)) {
            throw new FirewallException('Unable to detect configuration for authentication provider "' . $authProvider . '".'
            );
        }

        return $this->_authProviderConfig;
    }

    /**
     * Method that validates the submitted credentials with defined firewall user providers.
     * If authentication is valid, a user object is created and a token is stored.
     * This method just calls the 'authenticate' method on current user object, and if auth method returns true,
     * we create a token and return the user instance.
     *
     * @param Login $login
     *
     * @return bool|UserAbstract
     * @throws FirewallException
     */
    private function _authenticate(Login $login)
    {
        try {
            $user = $this->_getUserFromUserProvider($login);
        } catch (\Exception $e) {
            return false;
        }

        if ($user) {
            if ($user->authenticate($login, $this)) {
                // save info about current auth provider into user instance
                $user->setAuthProviderName($login->getAuthProviderName());

                // save token
                $this->getToken()->saveUser($user);

                return $user;
            } else {
                return false;
            }
        }

        return false;
    }

    /**
     * Tries to load user object from the registered user providers based on the data inside the Login object instance.
     *
     * @param Login $login Login object received from authentication provider.
     *
     * @return UserAbstract|bool Instance of UserAbstract, if user is found, or false if user is not found.
     * @throws FirewallException
     */
    private function _getUserFromUserProvider(Login $login)
    {
        foreach ($this->_userProviders as $provider) {
            try {
                $user = $provider->getUser($login);
                if ($user) {
                    $user->setAuthProviderName($login->getAuthProviderName());

                    return $user;
                }
            } catch (UserNotFoundException $e) {
                // next user provider
            } catch (\Exception $e) {
                throw new FirewallException($e->getMessage());
            }
        }

        return false;
    }

    /**
     * Initializes the Token.
     */
    private function _initToken()
    {
        $tokenName = $this->getConfig()->get('Token', false);
        if (!$tokenName) {
            throw new FirewallException('Token for "' . $this->_firewallKey . '" firewall is not defined.');
        }
        $rememberMe = $this->getConfig()->get('RememberMe', false);
        $securityKey = Security::getConfig()->get('Tokens.' . $tokenName . '.SecurityKey', false);

        if (!$securityKey) {
            throw new FirewallException('Missing security key for "' . $tokenName . '" token.');
        }
        $tokenCryptDriver = Security::getConfig()->get('Tokens.' . $tokenName . '.Driver', false);
        if (!$tokenCryptDriver) {
            throw new FirewallException('Driver parameter for token "' . $tokenName . '" is not defined.');
        }
        $tokenCryptParams = Security::getConfig()->get('Tokens.' . $tokenName . '.Params', [], true);
        try {
            $tokenCrypt = $this->factory($tokenCryptDriver,
                                         'Webiny\Component\Security\Token\CryptDrivers\CryptDriverInterface',
                                         $tokenCryptParams
            );
        } catch (\Exception $e) {
            throw new FirewallException($e->getMessage());
        }

        $this->_token = new Token($this->_getTokenName(), $rememberMe, $securityKey, $tokenCrypt);
    }

    /**
     * Returns the token name.
     *
     * @return string
     */
    private function _getTokenName()
    {
        return 'wf_token_' . $this->_firewallKey . '_realm';
    }

    /**
     * Get the authentication provider.
     *
     * @param string $authProvider Name of the auth provider you wish to use to process the login.
     *                             If you don't set it, the first registered provider will be used.
     *
     * @return AuthenticationInterface
     *
     * @throws FirewallException
     */
    private function _getAuthProvider($authProvider)
    {
        if (is_null($this->_authProvider)) {
            // auth provider config
            $authProviderConfig = $this->_getAuthProviderConfig($authProvider);

            // optional params that will be passed to auth provider constructor
            $params = $authProviderConfig->get('Params', [], true);

            try {
                $this->_authProvider = $this->factory($authProviderConfig->Driver,
                                                      '\Webiny\Component\Security\Authentication\Providers\AuthenticationInterface',
                                                      $params
                );
            } catch (Exception $e) {
                throw new FirewallException($e->getMessage());
            }
        }

        return $this->_authProvider;
    }

    /**
     * Initializes role hierarchy.
     */
    private function _initRoleHierarchy()
    {
        $this->_roleHierarchy = new RoleHierarchy($this->_config->get('RoleHierarchy', [], true));
    }

    /**
     * Sets roles for current user.
     */
    private function _setUserRoles()
    {
        $this->_initRoleHierarchy();
        $this->_user->setRoles($this->_roleHierarchy->getAccessibleRoles($this->_user->getRoles()));
    }
}