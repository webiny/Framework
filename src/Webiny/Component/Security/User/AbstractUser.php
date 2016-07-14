<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Security\User;

use Webiny\Component\Security\Authentication\Firewall;
use Webiny\Component\Security\Authentication\Providers\Login;
use Webiny\Component\Security\Role\Role;
use Webiny\Component\Security\Token\TokenData;
use Webiny\Component\StdLib\StdLibTrait;
use Webiny\Component\StdLib\StdObject\ArrayObject\ArrayObject;

/**
 * This is the abstract user class with common helpers functions for UserProviders.
 * You can optionally extend this class if you want to inherit the common getter functions.
 *
 * @package         Webiny\Component\Security\User
 */
abstract class AbstractUser implements UserInterface
{
    use StdLibTrait;

    /**
     * @var string Users username.
     */
    private $username = '';

    /**
     * @var string Users password.
     */
    private $password = '';

    /**
     * @var bool Is user authenticated flag.
     */
    private $isAuthenticated = false;

    /**
     * @var ArrayObject An list of user roles.
     */
    private $roles;

    /**
     * @var string The name of the auth provider that has authenticated the current user.
     */
    private $authProviderName = '';

    /**
     * @var string The name of the user provider that has provided the current user.
     */
    private $userProviderName = '';


    /**
     * This method verifies the credentials of current user with the credentials provided from the Login object.
     *
     * @param Login    $login
     * @param Firewall $firewall
     *
     * @return bool Return true if credentials are valid, otherwise return false.
     */
    abstract function authenticate(Login $login, Firewall $firewall);

    /**
     * Populate the user object.
     *
     * @param string $username        Username.
     * @param string $password        Hashed password.
     * @param array  $roles           Array of the assigned roles.
     * @param bool   $isAuthenticated Boolean flag that tells us if user is already authenticated or not.
     */
    public function populate($username, $password, array $roles, $isAuthenticated = false)
    {
        // store general data
        $this->username = $username;
        $this->password = $password;
        $this->isAuthenticated = $isAuthenticated;

        $this->roles = $this->arr([]);
        foreach ($roles as $r) {
            if($this->isInstanceOf($r, '\Webiny\Component\Security\Role\Role')) {
                $this->roles->append($r);
            } else {
                $this->roles->append(new Role($r));
            }
        }

        // append anonymous role
        $this->roles->append(new Role('ROLE_ANONYMOUS'));
    }

    /**
     * @return string Username.
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return string Hashed password.
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Get a list of assigned roles
     * @return array List of assigned roles.
     */
    public function getRoles()
    {
        return $this->roles->val();
    }

    /**
     * Check if current user has the defined role.
     *
     * @param string $role Role name
     *
     * @return bool True if user has the role, otherwise false.
     */
    public function hasRole($role)
    {
        foreach ($this->roles as $roleObj) {
            if($role == $roleObj->getRole()){
                return true;
            }
        }
        return false;
    }

    /**
     * Check if user is already authenticated.
     *
     * @return bool True if user is authenticated, otherwise false.
     */
    public function isAuthenticated()
    {
        return $this->isAuthenticated;
    }

    /**
     * Sets the auth flag.
     *
     * @param bool $bool
     */
    public function setIsAuthenticated($bool)
    {
        $this->isAuthenticated = $bool;
    }

    /**
     * This method compares the $tokenData against the current user and returns true if users are identical,
     * otherwise false is returned.
     *
     * @param TokenData $tokenData
     *
     * @return bool
     */
    public function isTokenValid(TokenData $tokenData)
    {
        $roles = [];
        foreach ($this->roles as $role) {
            $roles[] = $role->getRole();
        }
        $currentUser = $this->str($this->getUsername() . implode(',', $roles))->hash('md5');

        $tokenRoles = [];
        foreach ($tokenData->getRoles() as $role) {
            $tokenRoles[] = $role->getRole();
        }
        $tokenUser = $this->str($tokenData->getUsername() . implode(',', $tokenRoles))->hash('md5');

        return ($currentUser == $tokenUser);
    }

    /**
     * Sets the user roles.
     *
     * @param array $roles An array of Role instances.
     */
    public function setRoles(array $roles)
    {
        $this->roles = $this->arr($roles);
    }

    /**
     * Set the name of the auth provider.
     *
     * @param string $name
     */
    public function setAuthProviderName($name)
    {
        $this->authProviderName = $name;
    }

    /**
     * Returns the name of auth provider.
     *
     * @return string
     */
    public function getAuthProviderName()
    {
        return $this->authProviderName;
    }

    /**
     * Set the name of the user provider.
     *
     * @param string $name
     */
    public function setUserProviderName($name)
    {
        $this->userProviderName = $name;
    }

    /**
     * Returns the name of user provider.
     *
     * @return string
     */
    public function getUserProviderName()
    {
        return $this->userProviderName;
    }
}