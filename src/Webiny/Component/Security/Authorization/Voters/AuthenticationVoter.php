<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Security\Authorization\Voters;

use Webiny\Component\Security\Role\Role;
use Webiny\Component\Security\User\UserAbstract;

/**
 * Authentication voter checks if user is authorized or not.
 *
 * @package         Webiny\Component\Security\Authorization\Voters
 */
class AuthenticationVoter implements VoterInterface
{

    /**
     * This function is called before we ask the voter to vote.
     * The voter will get a list of user roles, and can loop through it to check if it supports the roles, eg. based
     * on their prefix.
     * If the voter returns false, we will not ask him to vote, if it returns true, the voter must vote.
     *
     * @param Role $role A Role instances that the current route requires.
     *
     * @return bool
     */
    public function supportsRole(Role $role)
    {
        return true;
    }

    /**
     * Voter method that checks if current voter supports the given User class.
     *
     * @param string $userClassName Fully qualified name of the user class with namespace.
     *
     * @return bool True if Voter supports the class, otherwise false.
     */
    public function supportsUserClass($userClassName)
    {
        return true;
    }

    /**
     * This function gets the current user object and needs to validate its access against the required roles.
     * The function must either return ACCESS_GRANTED, ACCESS_ABSTAIN or ACCESS_DENIED.
     *
     * @param UserAbstract $user           Current user instance.
     * @param array        $requestedRoles An array of requested roles for the current access map.
     *
     * @return integer ACCESS_GRANTED, ACCESS_ABSTAIN or ACCESS_DENIED.
     */
    public function vote(UserAbstract $user, array $requestedRoles)
    {
        $result = self::ACCESS_DENIED;
        if ($user->isAuthenticated()) {
            $result = self::ACCESS_GRANTED;
        }

        return $result;
    }
}
