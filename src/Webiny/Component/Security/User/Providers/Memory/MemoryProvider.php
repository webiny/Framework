<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Security\User\Providers\Memory;

use Webiny\Component\Security\User\Exceptions\UserNotFoundException;
use Webiny\Component\Security\Authentication\Providers\Login;
use Webiny\Component\Security\User\UserAbstract;
use Webiny\Component\Security\User\UserProviderInterface;
use Webiny\Component\StdLib\StdLibTrait;

/**
 * Memory user provider.
 * This provider is used when user accounts are defined inside the system configuration (hard-coded).
 *
 * @package        Webiny\Component\Security\User\Providers
 */
class MemoryProvider implements UserProviderInterface
{
    use StdLibTrait;

    /**
     * @var array Array with all users.
     */
    private $_users = [];

    /**
     * Constructor.
     */
    public function __construct()
    {
        $args = func_get_args();
        $this->_addUsers($args[0]);
    }

    /**
     * Check user data and, if valid, store them.
     *
     * @param array $users List of user accounts.
     *
     * @return bool
     * @throws MemoryException
     */
    private function _addUsers($users)
    {
        if (!is_array($users)) {
            return false;
        }

        foreach ($users as $username => $data) {
            if ($username == '' || !$this->isString($username)) {
                throw new MemoryException('Cannot store a user that doesn\'t have a username.');
            }

            if (!isset($data['password'])) {
                $data['password'] = '';
            }

            if (!isset($data['roles']) || empty($data['roles'])) {
                $data['roles'] = [];
            } else {
                $data['roles'] = (array)$data['roles'];
            }

            $this->_users[$username] = $data;
        }

        return true;
    }

    /**
     * Get the user from user provided for the given instance of Login object.
     * NOTE: The method gets the users based on his username only, password is not verified, this is part of
     * the authentication process.
     *
     * @param Login $login Instance of Login object.
     *
     * @return UserAbstract
     * @throws UserNotFoundException
     */
    public function getUser(Login $login)
    {
        $username = $login->getUsername();

        if (!isset($this->_users[$username]) || !$this->isArray($this->_users[$username])) {
            throw new UserNotFoundException('User "' . $username . '" was not found.');
        }

        $userData = $this->_users[$username];

        $user = new User();
        $user->populate($username, $userData['password'], $userData['roles'], false);

        return $user;
    }
}