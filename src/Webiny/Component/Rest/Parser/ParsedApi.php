<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Rest\Parser;

/**
 * ParsedApi class holds a one or more instances of a ParsedClass.
 *
 * @package         Webiny\Component\Rest\Parser
 */

class ParsedApi
{
    /**
     * @var string Name of the main api class. If version change, the main name is always the same.
     */
    public $apiClass;

    /**
     * @var array An array holding ParsedClass instances mapped to a version number.
     */
    public $versions = [];

    /**
     * @var string Version value for 'current' version alias.
     */
    public $currentVersion = '1.0';

    /**
     * @var string Version value for 'latest' version alias.
     */
    public $latestVersion = '1.0';


    /**
     * Base constructor.
     *
     * @var string Name of the main api class. If version change, the main name is always the same.
     */
    public function __construct($apiClass)
    {
        $this->apiClass = $apiClass;
    }


    /**
     * Adds an additional ParsedClass instance to the $versions array.
     *
     * @param ParsedClass $parsedClass Instance of ParsedClass.
     * @param string      $version     Version value.
     */
    public function addVersion(ParsedClass $parsedClass, $version)
    {
        $this->versions[$version] = $parsedClass;
    }

    /**
     * Sets the real version value for 'current' version alias.
     *
     * @param string $version Version value.
     */
    public function setCurrentVersion($version)
    {
        $this->currentVersion = $version;
    }

    /**
     * Sets the real version value for 'latest' version alias.
     *
     * @param string $version Version value.
     */
    public function setLatestVersion($version)
    {
        $this->latestVersion = $version;
    }
}