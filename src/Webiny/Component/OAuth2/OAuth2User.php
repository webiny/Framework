<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\OAuth2;

/**
 * This is the OAuth2 user class.
 * This class is returned when you request user details form an OAuth2 server.
 * This class standardizes the data that you get back because every OAuth2 server has its own user structure.
 *
 * @package         Webiny\Component\OAuth2
 */
class OAuth2User
{
    /**
     * @var string
     */
    public $username = '';
    /**
     * @var string
     */
    public $email = '';
    /**
     * @var string
     */
    public $profileUrl = '';
    /**
     * @var string
     */
    public $avatarUrl = '';
    /**
     * @var string
     */
    public $profileId = '';
    /**
     * @var string
     */
    public $firstName = '';
    /**
     * @var string
     */
    public $lastName = '';
    /**
     * @var int
     */
    public $lastUpdated = '';
    /**
     * @var int
     */
    public $gender = '';
    /**
     * @var int
     */
    public $serviceName = '';


    /**
     * Base constructor.
     *
     * @param string $username Users username.
     * @param string $email Users email.
     */
    public function __construct($username, $email)
    {
        $this->username = $username;
        $this->email = $email;
    }

    /**
     * Set the url of users profile on the current OAuth2 server.
     *
     * @param string $profileUrl
     */
    public function setProfileUrl($profileUrl)
    {
        $this->profileUrl = $profileUrl;
    }

    /**
     * Returns the url to users profile on the OAuth2 server.
     *
     * @return string
     */
    public function getProfileUrl()
    {
        return $this->profileUrl;
    }

    /**
     * Set the url to users avatar on the current OAuth2 server.
     *
     * @param string $avatarUrl
     */
    public function setAvatarUrl($avatarUrl)
    {
        $this->avatarUrl = $avatarUrl;
    }

    /**
     * Get the url to users avatar on the OAuth2 server.
     *
     * @return string
     */
    public function getAvatarUrl()
    {
        return $this->avatarUrl;
    }

    /**
     * Set the id of user of the current OAuth2 server.
     *
     * @param string $id
     */
    public function setProfileId($id)
    {
        $this->profileId = $id;
    }

    /**
     * Get user profile id on the current OAuth2 server.
     *
     * @return string
     */
    public function getProfileId()
    {
        return $this->profileId;
    }

    /**
     * Set users first name.
     *
     * @param string $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * Get users first name.
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set users last name.
     *
     * @param $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * Get users last name.
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Set the date when user last updated his profile on the OAuth2 server.
     *
     * @param int $timestamp Timestamp in milliseconds.
     */
    public function setLastUpdateTime($timestamp)
    {
        $this->lastUpdated = $timestamp;
    }

    /**
     * Get the date when user last updated his profile on the OAuth2 server.
     *
     * @return int Timestamp in milliseconds.
     */
    public function getLastUpdateTime()
    {
        return $this->lastUpdated;
    }

    /**
     * Set the service name that user used to login (like facebook, linkedin etc.)
     *
     * @param string $serviceName
     */
    public function setServiceName($serviceName)
    {
        $this->serviceName = $serviceName;
    }

    /**
     * Returns the name of the current OAuth2 server (like facebook, linkedin etc.).
     *
     * @return string
     */
    public function getServiceName()
    {
        return $this->serviceName;
    }

    /**
     * Set the gender of the user, can be 'male' or 'female'.
     *
     * @param $gender
     *
     * @throws OAuth2Exception
     */
    public function setGender($gender)
    {
        $gender = strtolower($gender);

        if ($gender != '' && $gender != 'male' && $gender != 'female') {
            throw new OAuth2Exception('Gender can be either "male" or "female", you tried to set it to "' . $gender . '".');
        }

        $this->gender = $gender;
    }

    /**
     * Get users gender.
     *
     * @return string
     */
    public function getGender()
    {
        return $this->gender;
    }
}