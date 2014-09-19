<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\TwitterOAuth;

/**
 * This is the TwitterOAuth user class.
 *
 * This class is returned when you request user details form an TwitterOAuth server.
 * This class standardizes the data that you get back because every TwitterOAuth server has its own user structure.
 *
 * @package         Webiny\Component\TwitterOAuth
 */
class TwitterOAuthUser
{
    /**
     * @var string
     */
    public $username = '';
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
    public $name = '';
    /**
     * @var int
     */
    public $lastUpdated = '';
    /**
     * @var string
     */
    public $location;
    /**
     * @var string
     */
    public $description;
    /**
     * @var string
     */
    public $website;
    /**
     * @var int
     */
    public $profileId;


    /**
     * Base constructor.
     *
     * @param string $username Users username.
     */
    public function __construct($username)
    {
        $this->username = $username;
    }

    /**
     * Returns current username.
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set the url of users profile on the current TwitterOAuth server.
     *
     * @param string $profileUrl
     */
    public function setProfileUrl($profileUrl)
    {
        $this->profileUrl = $profileUrl;
    }

    /**
     * Returns the url to users profile on the TwitterOAuth server.
     *
     * @return string
     */
    public function getProfileUrl()
    {
        return $this->profileUrl;
    }

    /**
     * Set the url to users avatar on the current TwitterOAuth server.
     *
     * @param string $avatarUrl
     */
    public function setAvatarUrl($avatarUrl)
    {
        $this->avatarUrl = $avatarUrl;
    }

    /**
     * Get the url to users avatar on the TwitterOAuth server.
     *
     * @return string
     */
    public function getAvatarUrl()
    {
        return $this->avatarUrl;
    }

    /**
     * Set the user location value.
     *
     * @param $location
     */
    public function setLocation($location)
    {
        $this->location = $location;
    }

    /**
     * Get user location.
     *
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set users first name.
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get users first name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the date when user last updated his profile on the TwitterOAuth server.
     *
     * @param int $timestamp Timestamp in milliseconds.
     */
    public function setLastUpdateTime($timestamp)
    {
        $this->lastUpdated = $timestamp;
    }

    /**
     * Get the date when user last updated his profile on the TwitterOAuth server.
     *
     * @return int Timestamp in milliseconds.
     */
    public function getLastUpdateTime()
    {
        return $this->lastUpdated;
    }

    /**
     * Set the users website url.
     *
     * @param string $website
     */
    public function setWebsite($website)
    {
        $this->website = $website;
    }

    /**
     * Get the user website.
     *
     * @return string
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * Set user profile description.
     *
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Get user profile description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the id of user of the current server.
     *
     * @param string $id
     */
    public function setProfileId($id)
    {
        $this->profileId = $id;
    }

    /**
     * Get user profile id on the current server.
     *
     * @return string
     */
    public function getProfileId()
    {
        return $this->profileId;
    }
}