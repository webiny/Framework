<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Config;

use Webiny\Component\Config\Drivers\AbstractDriver;
use Webiny\Component\Config\Drivers\IniDriver;
use Webiny\Component\Config\Drivers\JsonDriver;
use Webiny\Component\Config\Drivers\PhpDriver;
use Webiny\Component\Config\Drivers\YamlDriver;
use Webiny\Component\StdLib\StdObject\ArrayObject\ArrayObject;
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
     * @var ArrayObject
     */
    protected $data;

    /**
     * Cache key used to store this object to ConfigCache
     * @var string|null
     */
    private $cacheKey = null;

    /**
     * @var null|string
     */
    private $resourceType = null;

    /**
     * @var null|string
     */
    private $driverClass = null;

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

    public function getAs(AbstractDriver $driver)
    {
        return $driver->getString();
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

            if (!$this->data->keyExists($keys[0])) {
                return $default;
            }

            return $this->data->key($keys[0])->get($keys[1], $default, $toArray);
        }

        if ($this->data->keyExists($name)) {
            $result = $this->data->key($name);
            if ($toArray && ($result instanceof $this)) {
                $result = $result->toArray();
            }

            return $result;
        }

        return $default;
    }

    /**
     * Set config object value
     * You can also access deeper values by using dotted key notation: level1.level2.level3.key
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return $this|array|mixed|ArrayObject|StringObject
     */
    public function set($name, $value)
    {
        if ($this->str($name)->contains('.')) {
            $keys = $this->str($name)->trim('.')->explode('.', 2);

            if (!$this->data->keyExists($keys[0])) {
                $this->data->key($keys[0], new ConfigObject());
            }

            $this->data->key($keys[0])->set($keys[1], $value);

            return $this;
        }

        if (!$this->data->keyExists($name)) {
            $this->data->key($name, new ConfigObject());
        }

        $this->data->key($name, $value);

        return $this;
    }

    /**
     * ConfigObject is an object representing config data in an OO way
     *
     * @param  array|ArrayObject|AbstractDriver $resource Config resource
     *
     * @param bool                              $cache Store ConfigObject to cache or not
     *
     * @throws ConfigException
     */
    public function __construct($resource = [], $cache = true)
    {
        $driverAbstractClassName = '\Webiny\Component\Config\Drivers\AbstractDriver';
        $arrayObjectClassName = '\Webiny\Component\StdLib\StdObject\ArrayObject\ArrayObject';

        // Validate given resources
        if (!$this->isArray($resource) && !$this->isInstanceOf($resource, $driverAbstractClassName) && !$this->isArrayObject($resource)) {
            throw new ConfigException("ConfigObject resource must be a valid array, $arrayObjectClassName or $driverAbstractClassName");
        }


        if ($this->isInstanceOf($resource, $driverAbstractClassName)) {
            $originalResource = $resource->getResource();
            // Store driver class name
            $this->driverClass = get_class($resource);
            // Get driver to parse resource and return data array
            $resource = $resource->getArray();
        } else {
            $originalResource = $resource;
        }

        $this->resourceType = $this->determineResourceType($originalResource);

        // Build internal data array from array resource
        $this->buildInternalData($resource);

        // Store config to cache
        if ($cache) {
            $this->cacheKey = ConfigCache::createCacheKey($originalResource);
            ConfigCache::setCache($this->cacheKey, $this);
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
        foreach ($this->data as $k => $v) {
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
            $this->data[] = $value;
        } else {
            $this->data[$name] = $value;
        }

        // Update cache with new value
        ConfigCache::setCache($this->cacheKey, $this);
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($offset)
    {
        return $this->data->keyExists($offset);
    }

    /**
     * @inheritdoc
     */
    public function offsetGet($offset)
    {
        return $this->data->key($offset);
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value)
    {
        $this->data->key($offset, $value);
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset)
    {
        $this->data->removeKey($offset);
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
        return $this->data->keyExists($name);
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
        if ($this->data->keyExists($name)) {
            $this->data->removeKey($name);
        }
    }

    public function __toString()
    {
        return var_export($this->toArray(), true);
    }

    /**
     * @inheritdoc
     */
    public function getIterator()
    {
        return $this->data->getIterator();
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
        if (self::isStdObject($resource)) {
            $resource = $resource->val();
        }

        if (self::isArray($resource)) {
            return self::ARRAY_RESOURCE;
        } elseif (self::isFile($resource)) {
            return self::FILE_RESOURCE;
        } elseif (self::isString($resource)) {
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
            throw new ConfigException('Invalid parameter passed to ConfigObject mergeWith($config) method! Expecting a ConfigObject or array.');
        }

        /** @var ConfigObject $value */
        foreach ($configs as $config) {
            // If it's a PHP array or ArrayObject, convert it to ConfigObject
            if ($this->isArray($config) || $this->isArrayObject($config)) {
                $config = Config::getInstance()->php($config);
            }

            foreach ($config as $key => $value) {
                if ($this->data->keyExists($key)) {
                    if ($this->isNumber($key)) {
                        $this->data[] = $value;
                    } elseif ($this->isInstanceOf($value, $this) && $this->isInstanceOf($this->data[$key], $this)) {
                        $this->data[$key]->mergeWith($value);
                    } else {
                        if ($this->isInstanceOf($value, $this)) {
                            $this->data[$key] = new static($value->toArray(), false);
                        } else {
                            $this->data[$key] = $value;
                        }
                    }
                } else {
                    if ($this->isInstanceOf($value, $this)) {
                        $this->data[$key] = new static($value->toArray(), false);
                    } else {
                        $this->data[$key] = $value;
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
    private function buildInternalData($config)
    {
        $this->data = $this->arr();
        $array = StdObjectWrapper::toArray($config);
        foreach ($array as $key => $value) {
            if ($this->isArray($value)) {
                $this->data->key($key, new static($value, false));
            } else {
                $this->data->key($key, $value, true);
            }
        }
    }

    public function serialize()
    {
        $data = [
            'data'         => [],
            'resourceType' => $this->resourceType,
            'driverClass'  => $this->driverClass,
            'cacheKey'     => $this->cacheKey
        ];
        $data['data'] = $this->data;

        return serialize($data);
    }

    public function unserialize($string)
    {
        $data = unserialize($string);
        $this->cacheKey = $data['cacheKey'];
        $this->driverClass = $data['driverClass'];
        $this->resourceType = $data['resourceType'];
        $this->data = new ArrayObject($data['data']);
    }

    public function __wakeup()
    {
        ConfigCache::setCache($this->cacheKey, $this);
    }
}
