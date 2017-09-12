<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Config\Bridge\Yaml;

use Webiny\Component\Config\Bridge\Yaml\SymfonyYaml\SymfonyYaml;
use Webiny\Component\StdLib\Exception\AbstractException;
use Webiny\Component\StdLib\StdLibTrait;
use Webiny\Component\StdLib\StdObject\StdObjectWrapper;

/**
 * Bridge for Yaml parser
 *
 * @package   Webiny\Component\Config\Bridge\Yaml
 */
class Yaml implements YamlInterface
{
    use StdLibTrait;

    /**
     * @var YamlInterface
     */
    private $driverInstance = null;

    /**
     * Default Yaml driver class name
     * @var string
     */
    private static $driverClass = SymfonyYaml::class;

    /**
     * Instance of Yaml driver to use
     * @var null|YamlInterface
     */
    private static $customDriver = null;

    /**
     * Set Yaml driver to use by Yaml bridge
     *
     * @param $driver string|YamlInterface
     *
     * @throws YamlException
     */
    public static function setDriver($driver)
    {

        if (!self::isInstanceOf($driver, YamlInterface::class)) {
            if (self::isString($driver) || self::isStringObject($driver)) {
                $driver = StdObjectWrapper::toString($driver);
                $driver = new $driver;
                if (self::isInstanceOf($driver, YamlInterface::class)) {
                    self::$customDriver = $driver;

                    return;
                }
            }
            throw new YamlException(AbstractException::MSG_INVALID_ARG, ['$driver', YamlInterface::class]);
        }
        self::$customDriver = $driver;

        return;
    }

    /**
     * Get bridge instance
     *
     * @param mixed $resource
     *
     * @return YamlInterface
     */
    public static function getInstance($resource = null)
    {
        return new static($resource);
    }

    /**
     * Get current Yaml value as string
     *
     * @param int  $indent
     * @param bool $wordWrap
     *
     * @throws YamlException
     * @return string
     */
    public function getString($indent = 2, $wordWrap = false)
    {
        $res = $this->driverInstance->getString($indent, $wordWrap);
        if (!$this->isString($res) && !$this->isStringObject($res)) {
            throw new YamlException('YamlInterface method _getString() must return a string or StringObject.');
        }

        return StdObjectWrapper::toString($res);
    }

    /**
     * Get Yaml value as array
     *
     * @throws YamlException
     * @return array
     */
    public function getArray()
    {
        $res = $this->driverInstance->getArray();
        if (!$this->isArray($res) && !$this->isArrayObject($res)) {
            throw new YamlException('YamlInterface method getArray() must return an array or ArrayObject.');
        }

        return StdObjectWrapper::toArray($res);
    }

    /**
     * Set driver resource to work on
     *
     * @param mixed $resource
     *
     * @throws YamlException
     * @return $this
     */
    public function setResource($resource)
    {
        $res = $this->driverInstance->setResource($resource);
        if (!$this->isInstanceOf($res, YamlInterface::class)) {
            throw new YamlException('YamlInterface method setSource() must return YamlInterface object.');
        }

        return $this;
    }

    /**
     * Create Yaml bridge
     *
     * @param mixed $resource
     */
    private function __construct($resource = null)
    {
        if ($this->isInstanceOf(self::$customDriver, YamlInterface::class)) {
            // If custom driver instance was set, we need to use a copy of it and set it's resource
            $this->driverInstance = clone $this->driverInstance->setResource($resource);
        } else {
            $this->driverInstance = new self::$driverClass();
            $this->driverInstance->setResource($resource);
        }
    }
}