<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Security\User;

/**
 * User interface.
 * Every user provider User class must implement this interface.
 *
 * @package         Webiny\Component\Security\User
 */

interface UserInterface
{

    /**
     * @return string Username.
     */
    public function getUsername();

    /**
     * @return string Hashed password.
     */
    public function getPassword();

    /**
     * Get a list of assigned roles
     * @return array List of assigned roles.
     */
    public function getRoles();
}