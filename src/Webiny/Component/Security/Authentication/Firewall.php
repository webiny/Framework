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
use Webiny\Component\Security\User\Providers\Memory;
use Webiny\Component\Security\Token\Token;
use Webiny\Component\Security\User\AbstractUser;
use Webiny\Component\StdLib\Exception\Exception;
use Webiny\Component\StdLib\FactoryLoaderTrait;
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
    private $config;

    /**
     * @var array An array of user provider instances.
     */
    private $userProviders = [];

    /**
     * @var string Name of the current firewall.
     */
    private $firewallKey;

    /**
     * @var \Webiny\Component\Security\Encoder\Encoder
     */
    private $encoder;

    /**
     * @var Token
     */
    private $token;

    /**
     * @var bool|AbstractUser
     */
    private $user = false;

    /**
     * @var bool Has user already been authenticated.
     */
    private $userAuthenticated = false;

    /**
     * @var AuthenticationInterface
     */
    private $authProvider;

    /**
     * @var \Webiny\Component\Config\ConfigObject
     */
    private $authProviderConfig;

    /**
     * @var RoleHierarchy
     */
    private $roleHierarchy;

    /**
     * @var AccessControl
     */
    private $accessControl;

    /**
     * @var string
     */
    private $defaultCryptDriver = '\Webiny\Component\Security\Token\CryptDrivers\Crypt\Crypt';

    /**
     * @var string
     */
    private $cryptDriverInterface = 'Webiny\Component\Security\Token\CryptDrivers\CryptDriverInterface';

    /**
     * @var string
     */
    private $authenticationInterface = '\Webiny\Component\Security\Authentication\Providers\AuthenticationInterface';

    /**
     * @var array A list of currently built-in authentication providers. The keys are used so you don't need to write
     *            the fully qualified class names in the yaml config.
     */
    private static $authProviders = [
        'Http'         => '\Webiny\Component\Security\Authentication\Providers\Http\Http',
        'Form'         => '\Webiny\Component\Security\Authentication\Providers\Form\Form',
        'OAuth2'       => '\Webiny\Component\Security\Authentication\Providers\OAuth2\OAuth2',
        'TwitterOAuth' => '\Webiny\Component\Security\Authentication\Providers\TwitterOAuth\TwitterOAuth'
    ];


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
        $this->firewallKey = $firewallKey;
        $this->config = $firewallConfig;
        $this->userProviders = $userProviders;
        $this->encoder = $encoder;

        $this->initToken();
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
            $login = $this->getAuthProvider($authProvider)->getLoginObject($this->getConfig());
            if (!$this->isInstanceOf($login, 'Webiny\Component\Security\Authentication\Providers\Login')) {
                throw new FirewallException('Authentication provider method getLoginObject() must return an instance of
														"Webiny\Component\Security\Authentication\Providers\Login".');
            }
            $login->setAuthProviderName($this->authProviderName);
        } catch (\Exception $e) {
            throw new FirewallException($e->getMessage());
        }

        // forward the login object to user providers and validate the credentials
        $this->user = $this->authenticate($login);

        if (!$this->user) { // login failed
            $this->getAuthProvider($authProvider)->invalidLoginProvidedCallback();
            $this->eventManager()->fire(SecurityEvent::LOGIN_INVALID, new SecurityEvent(new AnonymousUser()));

            return false;
        } else {
            $this->getAuthProvider($authProvider)->loginSuccessfulCallback($this->user);
            $this->eventManager()->fire(SecurityEvent::LOGIN_VALID, new SecurityEvent($this->user));
            $this->setUserRoles();
            $this->userAuthenticated = true;

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
            $this->getAuthProvider($this->user->getAuthProviderName())->logoutCallback();
        }
        $this->user = new AnonymousUser();
        $this->userAuthenticated = false;

        $this->eventManager()->fire(SecurityEvent::LOGOUT);

        return true;
    }

    /**
     * Tries to retrieve the user from current token.
     * If the token does not exist, AnonymousUser is returned.
     *
     * @throws FirewallException
     * @return bool|\Webiny\Component\Security\User\AbstractUser
     */
    public function getUser()
    {
        if ($this->userAuthenticated) {
            return $this->user;
        }

        try {
            // get token
            $this->user = new AnonymousUser();
            $tokenData = $this->getToken()->getUserFromToken();

            if (!$tokenData) {
                $this->eventManager()->fire(SecurityEvent::NOT_AUTHENTICATED, new SecurityEvent($this->user));

                $this->userAuthenticated = false;

                return $this->user;
            } else {
                $this->user->populate($tokenData->getUsername(), '', $tokenData->getRoles(), true);
                $this->user->setAuthProviderName($tokenData->getAuthProviderName());
                $this->user->setUserProviderName($tokenData->getUserProviderName());
                $this->eventManager()->fire(SecurityEvent::AUTHENTICATED, new SecurityEvent($this->user));
                $this->setUserRoles();

                $this->userAuthenticated = true;

                return $this->user;
            }
        } catch (\Exception $e) {
            $this->userAuthenticated = true;
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
        if (!is_object($this->accessControl)) {
            $this->accessControl = new AccessControl($this->user, $this->config->get('AccessControl', false));
        }

        $isAccessAllowed = $this->accessControl->isUserAllowedAccess();
        if (!$isAccessAllowed) {
            $this->eventManager()->fire(SecurityEvent::ROLE_INVALID, new SecurityEvent($this->user));
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
        return $this->config->RealmName;
    }

    /**
     * Check if anonymous access is allowed or not.
     * If anonymous access is not defined in the config, by default it will be set to false.
     *
     * @return bool Is anonymous access allowed or not.
     */
    public function getAnonymousAccess()
    {
        return $this->config->get('Anonymous', false);
    }

    /**
     * Get config for current firewall.
     *
     * @return ConfigObject
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Get the current token.
     *
     * @return Token
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Returns the name of the current firewall. Don't mistake it for realm name.
     * @return string
     */
    public function getFirewallKey()
    {
        return $this->firewallKey;
    }

    /**
     * Create a hash for the given password.
     *
     * @param string $password
     *
     * @return string Password hash.
     */
    public function createPasswordHash($password)
    {
        return $this->encoder->createPasswordHash($password);
    }

    /**
     * Verify if the $password matches the $hash.
     *
     * @param string $password
     * @param string $hash
     *
     * @return bool True if $password matches $hash. Otherwise false is returned.
     */
    public function verifyPasswordHash($password, $hash)
    {
        return $this->encoder->verifyPasswordHash($password, $hash);
    }

    /**
     * Returns the config of current auth provider.
     *
     * @param string $authProvider Name of the auth provider you wish to use to process the login.
     *                             If you don't set it, the first registered provider will be used.
     *
     * @throws FirewallException
     * @return ConfigObject
     */
    private function getAuthProviderConfig($authProvider)
    {
        // have we already fetched the auth config
        if ($this->authProviderConfig) {
            return $this->authProviderConfig;
        }

        if ($authProvider == '') {
            // get the first auth provider from the list
            $providers = $this->getConfig()->get('AuthenticationProviders', []);
            $authProvider = $providers[0];
        }

        $this->authProviderConfig = Security::getConfig()->get('AuthenticationProviders.' . $authProvider, new ConfigObject());

        // merge the internal driver
        // merge only if driver is not set and it matches the internal auth provider name
        if (!$this->authProviderConfig->get('Driver', false) && isset(self::$authProviders[$authProvider])) {
            $this->authProviderConfig->mergeWith(['Driver' => self::$authProviders[$authProvider]]);
        }

        // make sure the requested auth provider is assigned to the current firewall
        if (!in_array($authProvider, $this->getConfig()->get('AuthenticationProviders', [])->toArray())) {
            throw new FirewallException('Authentication provider "' . $authProvider . '" is not defined on "' . $this->getFirewallKey() . '" firewall.');
        }

        // check that we have the driver
        if (!$this->authProviderConfig->get('Driver', false)) {
            throw new FirewallException('Unable to detect configuration for authentication provider "' . $authProvider . '".');
        }

        $this->authProviderName = $authProvider;

        return $this->authProviderConfig;
    }

    /**
     * Method that validates the submitted credentials with defined firewall user providers.
     * If authentication is valid, a user object is created and a token is stored.
     * This method just calls the 'authenticate' method on current user object, and if auth method returns true,
     * we create a token and return the user instance.
     *
     * @param Login $login
     *
     * @return bool|AbstractUser
     * @throws FirewallException
     */
    private function authenticate(Login $login)
    {
        foreach ($this->userProviders as $name => $provider) {
            try {
                /* @var AbstractUser $user */
                $user = $provider->getUser($login);
                if ($user && $user->authenticate($login, $this)) {
                    $user->setUserProviderName($name);
                    $user->setAuthProviderName($login->getAuthProviderName());
                    $this->getToken()->setRememberMe($login->getRememberMe())->saveUser($user);

                    return $user;
                }
            } catch (\Exception $e) {
                // next user provider
            }
        }

        return false;
    }

    /**
     * Initializes the Token.
     */
    private function initToken()
    {
        $tokenName = $this->getConfig()->get('Token', false);
        $rememberMe = $this->getConfig()->get('RememberMe', false);


        if (!$tokenName) {
            // fallback to the default token
            $securityKey = $this->getConfig()->get('TokenKey', false);

            if (!$securityKey) {
                throw new FirewallException('Missing TokenKey for "' . $this->getRealmName() . '" firewall.');
            }
        } else {
            $securityKey = Security::getConfig()->get('Tokens.' . $tokenName . '.SecurityKey', false);

            if (!$securityKey) {
                throw new FirewallException('Missing security key for "' . $tokenName . '" token.');
            }
        }

        $tokenCryptDriver = Security::getConfig()->get('Tokens.' . $tokenName . '.Driver', $this->defaultCryptDriver);
        $tokenCryptParams = Security::getConfig()->get('Tokens.' . $tokenName . '.Params', [], true);
        try {
            $tokenCrypt = $this->factory($tokenCryptDriver, $this->cryptDriverInterface, $tokenCryptParams);
        } catch (\Exception $e) {
            throw new FirewallException($e->getMessage());
        }

        $storageClass = Security::getConfig()->get('Tokens.' . $tokenName . '.StorageDriver');
        $this->token = new Token($this->getTokenName(), $rememberMe, $securityKey, $tokenCrypt, $storageClass);
    }

    /**
     * Returns the token name.
     *
     * @return string
     */
    private function getTokenName()
    {
        return strtolower($this->firewallKey) . '_token';
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
    private function getAuthProvider($authProvider)
    {
        if (is_null($this->authProvider)) {
            // auth provider config
            $authProviderConfig = $this->getAuthProviderConfig($authProvider);

            // optional params that will be passed to auth provider constructor
            $params = $authProviderConfig->get('Params', [], true);

            try {
                $this->authProvider = $this->factory($authProviderConfig->Driver, $this->authenticationInterface, $params);
            } catch (Exception $e) {
                throw new FirewallException($e->getMessage());
            }
        }

        return $this->authProvider;
    }

    /**
     * Initializes role hierarchy.
     */
    private function initRoleHierarchy()
    {
        $this->roleHierarchy = new RoleHierarchy($this->config->get('RoleHierarchy', [], true));
    }

    /**
     * Sets roles for current user.
     */
    private function setUserRoles()
    {
        $this->initRoleHierarchy();
        $this->user->setRoles($this->roleHierarchy->getAccessibleRoles($this->user->getRoles()));
    }
}