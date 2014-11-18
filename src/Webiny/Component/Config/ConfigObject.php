<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Config;

use Serializable;
use Traversable;
use Webiny\Component\Config\Drivers\DriverAbstract;
use Webiny\Component\Config\Drivers\IniDriver;
use Webiny\Component\Config\Drivers\JsonDriver;
use Webiny\Component\Config\Drivers\PhpDriver;
use Webiny\Component\Config\Drivers\YamlDriver;
use Webiny\Component\StdLib\StdObject\ArrayObject\ArrayObject;
use Webiny\Component\StdLib\StdObject\FileObject\FileObject;
use Webiny\Component\StdLib\StdObject\StdObjectWrapper;
use Webiny\Component\StdLib\StdObject\StringObject\StringObject;
use Webiny\Component\StdLib\ValidatorTrait;
use Webiny\Component\StdLib\StdObjectTrait;

/**
 * ConfigObject class holds config data in an OO way.
 *
 * @package         Webiny\Component\Config
 */
class ConfigObject implements \ArrayAccess, \IteratorAggregate
{
    use StdObjectTrait, ValidatorTrait;

    const ARRAY_RESOURCE = 'array';
    const STRING_RESOURCE = 'string';
    const FILE_RESOURCE = 'file';

    /**
     * Config data
     *
     * @var array
     */
    protected $_data = array();

    /**
     * Cache key used to store this object to ConfigCache
     * @var string|null
     */
    private $_cacheKey = null;

    /**
     * File resource that was used to build this config data
     * @var string|StringObject|FileObject|null
     */
    private $_fileResource = null;

    /**
     * @var null|string
     */
    private $_resourceType = null;

    /**
     * @var null|string
     */
    private $_driverClass = null;

    /**
     * GET METHODS
     */

    /**
     * Get config as Yaml string
     *
     * @param int $indent
     *
     * @return string
     */
    public function getAsYaml($indent = 4)
    {
        $driver = new YamlDriver($this->toArray());

        return $driver->setIndent($indent)->getString();
    }

    public function getAsPhp()
    {
        $driver = new PhpDriver($this->toArray());

        return $driver->getString();
    }

    public function getAsIni($useSections = true, $nestDelimiter = '.')
    {
        $driver = new IniDriver($this->toArray());

        return $driver->setDelimiter($nestDelimiter)->useSections($useSections)->getString();
    }

    public function getAsJson()
    {
        $driver = new JsonDriver($this->toArray());

        return $driver->getString();
    }

    public function getAs(DriverAbstract $driver)
    {
        return $driver->getString();
    }

    /**
     * SAVE METHODS
     */

    /**
     * Save config as Yaml
     *
     * @param     $destination
     * @param int $indent
     *
     * @internal param bool $wordWrap
     *
     * @return $this
     */

    public function saveAsYaml($destination, $indent = 4)
    {
        $driver = new YamlDriver($this->toArray());
        $driver->setIndent($indent)->saveToFile($destination);

        return $this;
    }

    public function saveAsPhp($destination)
    {
        $driver = new PhpDriver($this->toArray());
        $driver->saveToFile($destination);

        return $this;
    }

    public function saveAsJson($destination)
    {
        $driver = new JsonDriver($this->toArray());
        $driver->saveToFile($destination);

        return $this;
    }

    public function saveAsIni($destination, $useSections = true, $nestDelimiter = '.')
    {
        $driver = new IniDriver($this->toArray());
        $driver->useSections($useSections)->setDelimiter($nestDelimiter)->saveToFile($destination);

        return $this;
    }

    /**
     * Save config using given DriverAbstract instance
     *
     * @param DriverAbstract                 $driver
     * @param string|StringObject|FileObject $destination
     *
     * @return $this
     */
    public function saveAs(DriverAbstract $driver, $destination)
    {
        $driver->setResource($this->toArray())->saveToFile($destination);

        return $this;
    }

    /**
     * Save current config
     * @throws ConfigException
     * @return $this
     */
    public function save()
    {
        if ($this->_resourceType != ConfigObject::FILE_RESOURCE) {
            throw new ConfigException('ConfigObject was not created from a file resource and thus can not be saved directly!'
            );
        }

        $driver = new $this->_driverClass($this->toArray());
        $driver->saveToFile($this->_fileResource);

        return $this;

    }

    /**
     * Get value or return $default if there is no element set.
     * You can also access deeper values by using dotted key notation: level1.level2.level3.key
     *
     * @param string $name
     * @param mixed  $default
     * @param bool   $toArray
     *
     * @return mixed|ConfigObject Config value or default value
     */
    public function get($name, $default = null, $toArray = false)
    {
        if ($this->str($name)->contains('.')) {
            $keys = $this->str($name)->trim('.')->explode('.', 2);

            if ($this->_data->keyExists($keys[0])) {
                $value = $this->_data->key($keys[0])->get($keys[1], $default, $toArray);
            } else {
                return $default;
            }

            return $value;
        }

        if ($this->_data->keyExists($name)) {
            $result = $this->_data->key($name);
            if ($toArray && ($result instanceof $this)) {
                $result = $result->toArray();
            }

            return $result;
        }

        return $default;
    }

    /**
     * ConfigObject is an object representing config data in an OO way
     *
     * @param  array|ArrayObject|DriverAbstract $resource Config resource
     *
     * @param bool                              $cache    Store ConfigObject to cache or not
     *
     * @throws ConfigException
     */
    public function __construct($resource, $cache = true)
    {

        $driverAbstractClassName = '\Webiny\Component\Config\Drivers\DriverAbstract';
        $arrayObjectClassName = '\Webiny\Component\StdLib\StdObject\ArrayObject\ArrayObject';

        // Validate given resources
        if (!$this->isArray($resource) && !$this->isInstanceOf($resource, $driverAbstractClassName
            ) && !$this->isArrayObject($resource)
        ) {
            throw new ConfigException("ConfigObject resource must be a valid array, $arrayObjectClassName or $driverAbstractClassName"
            );
        }


        if ($this->isInstanceOf($resource, $driverAbstractClassName)) {
            $originalResource = $resource->getResource();
            // Store driver class name
            $this->_driverClass = get_class($resource);
            // Get driver to parse resource and return data array
            $resource = $resource->getArray();
        } else {
            $originalResource = $resource;
        }

        $this->_resourceType = self::determineResourceType($originalResource);
        if ($this->_resourceType == self::FILE_RESOURCE) {
            $this->_fileResource = $originalResource;
        }

        // Build internal data array from array resource
        $this->_buildInternalData($resource);

        // Store config to cache
        if ($cache) {
            $this->_cacheKey = ConfigCache::createCacheKey($originalResource);
            ConfigCache::setCache($this->_cacheKey, $this);
        }
    }

    /**
     * Get Config data in form of an array or ArrayObject
     *
     * @param bool $asArrayObject (Optional) Defaults to false
     *
     * @return array|ArrayObject Config data array or ArrayObject
     */
    public function toArray($asArrayObject = false)
    {
        $data = [];
        foreach ($this->_data as $k => $v) {
            if ($this->isInstanceOf($v, $this)) {
                $data[$k] = $v->toArray();
            } else {
                $data[$k] = $v;
            }
        }

        if ($asArrayObject) {
            return $this->arr($data);
        }

        return $data;
    }

    /**
     * Access internal data as if it was a real object
     *
     * @param  string $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * Set internal data as if it was a real object
     *
     * @param  string $name
     * @param  mixed  $value
     *
     * @return void
     */
    public function __set($name, $value)
    {
        if ($this->isArray($value)) {
            $value = new static($value);
        }

        if ($this->isNull($name)) {
            $this->_data[] = $value;
        } else {
            $this->_data[$name] = $value;
        }

        // Update cache with new value
        ConfigCache::setCache($this->_cacheKey, $this);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param mixed $offset <p>
     *                      An offset to check for.
     *                      </p>
     *
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     *       The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset)
    {
        return $this->_data->keyExists($offset);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param mixed $offset <p>
     *                      The offset to retrieve.
     *                      </p>
     *
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        return $this->_data->key($offset);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param mixed $offset <p>
     *                      The offset to assign the value to.
     *                      </p>
     * @param mixed $value  <p>
     *                      The value to set.
     *                      </p>
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->_data->key($offset, $value);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param mixed $offset
     * @param mixed $offset <p>
     *                      The offset to unset.
     *                      </p>
     *
     * @return void
     */
    public function offsetUnset($offset)
    {
        $this->_data->removeKey($offset);
    }

    /**
     * Override __isset
     *
     * @param  string $name
     *
     * @return bool
     */
    public function __isset($name)
    {
        return $this->_data->keyExists($name);
    }

    /**
     * Override __unset
     *
     * @param  string $name
     *
     * @return void
     */
    public function __unset($name)
    {
        if ($this->_data->keyExists($name)) {
            $this->_data->removeKey($name);
        }
    }

    public function __toString()
    {
        return var_export($this->toArray(), true);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     */
    public function getIterator()
    {
        return $this->_data->getIterator();
    }

    /**
     * Determine type of given resource
     *
     * @param $resource
     *
     * @return string
     * @throws ConfigException
     */
    public static function determineResourceType($resource)
    {
        if (self::isArray($resource) || self::isArrayObject($resource)) {
            return self::ARRAY_RESOURCE;
        } elseif (self::isFile($resource) || self::isFileObject($resource)) {
            return self::FILE_RESOURCE;
        } elseif (self::isString($resource) || self::isStringObject($resource)) {
            return self::STRING_RESOURCE;
        }
        throw new ConfigException("Given ConfigObject resource is not allowed!");
    }

    /**
     * Merge current config with given config
     *
     * @param array|ArrayObject|ConfigObject $config ConfigObject or array of ConfigObject to merge with
     *
     * @throws ConfigException
     * @return $this
     */
    public function mergeWith($config)
    {
        if ($this->isArray($config) || $this->isArrayObject($config)) {
            $configs = StdObjectWrapper::toArray($config);
            // Make sure it's an array of ConfigObject
            if (!$this->isInstanceOf($this->arr($configs)->first()->val(), $this)) {
                $configs = [Config::getInstance()->php($configs)];
            }
        } elseif ($this->isInstanceOf($config, $this)) {
            $configs = [$config];
        } else {
            throw new ConfigException('Invalid parameter passed to ConfigObject mergeWith($config) method! Expecting a ConfigObject or array.'
            );
        }

        /** @var ConfigObject $value */
        foreach ($configs as $config) {
            // If it's a PHP array or ArrayObject, convert it to ConfigObject
            if ($this->isArray($config) || $this->isArrayObject($config)) {
                $config = Config::getInstance()->php($config);
            }

            foreach ($config as $key => $value) {
                if ($this->_data->keyExists($key)) {
                    if ($this->isNumber($key)) {
                        $this->_data[] = $value;
                    } elseif ($this->isInstanceOf($value, $this) && $this->isInstanceOf($this->_data[$key], $this)) {
                        $this->_data[$key]->mergeWith($value);
                    } else {
                        if ($this->isInstanceOf($value, $this)) {
                            $this->_data[$key] = new static($value->toArray(), false);
                        } else {
                            $this->_data[$key] = $value;
                        }
                    }
                } else {
                    if ($this->isInstanceOf($value, $this)) {
                        $this->_data[$key] = new static($value->toArray(), false);
                    } else {
                        $this->_data[$key] = $value;
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Build internal object data using given $config
     *
     * @param array|ArrayObject $config
     */
    private function _buildInternalData($config)
    {
        $this->_data = $this->arr();
        $array = StdObjectWrapper::toArray($config);
        foreach ($array as $key => $value) {
            if ($this->isArray($value)) {
                $this->_data->key($key, new static($value, false));
            } else {
                $this->_data->key($key, $value, true);
            }
        }
    }

    public function serialize()
    {
        $data = [
            'data'         => [],
            'fileResource' => $this->_fileResource,
            'resourceType' => $this->_resourceType,
            'driverClass'  => $this->_driverClass,
            'cacheKey'     => $this->_cacheKey
        ];
        $data['data'] = $this->_data;

        return serialize($data);
    }

    public function unserialize($string)
    {
        $data = unserialize($string);
        $this->_cacheKey = $data['cacheKey'];
        $this->_fileResource = $data['fileResource'];
        $this->_driverClass = $data['driverClass'];
        $this->_resourceType = $data['resourceType'];
        $this->_data = new ArrayObject($data['data']);
    }

    public function __wakeup()
    {
        ConfigCache::setCache($this->_cacheKey, $this);
    }
}
