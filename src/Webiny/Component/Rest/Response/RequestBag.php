<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Rest\Response;

use Webiny\Component\Config\ConfigObject;

/**
 * RequestBag is just a transportation box for some of the api data.
 * Mainly used so we don't type constructors and function calls with 5-6 parameters...this makes it nicer
 * and more practical :)
 *
 * @package         Webiny\Component\Rest\Response
 */
class RequestBag
{
    /**
     * @var string Rest api configuration name.
     */
    private $api;

    /**
     * @var ConfigObject The actual Rest api configuration.
     */
    private $apiConfig;

    /**
     * @var array An array holding information about the class.
     */
    private $classData;

    /**
     * @var array An array holding information about the api method.
     */
    private $methodData;

    /**
     * @var array List of api method parameters and their values.
     */
    private $methodParameters;

    /**
     * @var mixed Actual instance of the api class.
     */
    private $classInstance;

    /**
     * @var string Path to the compiled cache file.
     */
    private $compileCacheFile;


    /**
     * Set the api configuration name.
     *
     * @param string $api Api configuration name.
     *
     * @return $this
     */
    public function setApi($api)
    {
        $this->api = $api;

        return $this;
    }

    /**
     * Get the api configuration name.
     *
     * @return string
     */
    public function getApi()
    {
        return $this->api;
    }

    /**
     * Get the api configuration.
     * Note: api name must already be set.
     *
     * @return ConfigObject
     */
    public function getApiConfig()
    {
        if (empty($this->apiConfig)) {
            $this->apiConfig = \Webiny\Component\Rest\Rest::getConfig()->{$this->api};
        }

        return $this->apiConfig;
    }

    /**
     * Set the api class data array.
     *
     * @param array $classData Api class data array.
     *
     * @return $this
     */
    public function setClassData($classData)
    {
        $this->classData = $classData;

        return $this;
    }

    /**
     * Get the class data array.
     *
     * @return array
     */
    public function getClassData()
    {
        return $this->classData;
    }

    /**
     * Set the method data array.
     *
     * @param array $methodData Method data array
     *
     * @return $this
     */
    public function setMethodData($methodData)
    {
        $this->methodData = $methodData;

        return $this;
    }

    /**
     * Return the method data array.
     *
     * @return array
     */
    public function getMethodData()
    {
        return $this->methodData;
    }

    /**
     * Set the array holding the information about the method parameters.
     *
     * @param array $methodParameters Array holding the information about the method parameters.
     *
     * @return $this
     */
    public function setMethodParameters($methodParameters)
    {
        $this->methodParameters = $methodParameters;

        return $this;
    }

    /**
     * Returns the array holding the information about the method parameters.
     *
     * @return array
     */
    public function getMethodParameters()
    {
        return $this->methodParameters;
    }

    /**
     * Sets the class instance.
     *
     * @param mixed $classInstance Api class instance.
     *
     * @return $this
     */
    public function setClassInstance($classInstance)
    {
        $this->classInstance = $classInstance;

        return $this;
    }

    /**
     * Get the api class instance.
     *
     * @return mixed
     */
    public function getClassInstance()
    {
        return $this->classInstance;
    }

}