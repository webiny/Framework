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
     * @var array
     */
    protected $data;

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
        if (strpos($name, '.') !== false) {
            $keys = explode('.', trim($name, '.'), 2);

            if (!array_key_exists($keys[0], $this->data)) {
                return $default;
            }

            return $this->data[$keys[0]]->get($keys[1], $default, $toArray);
        }

        if (array_key_exists($name, $this->data)) {
            $result = $this->data[$name];
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
        if (strpos($name, '.') !== false) {
            $keys = explode('.', trim($name, '.'), 2);

            if (!array_key_exists($keys[0], $this->data)) {
                $this->data[$keys[0]] = new ConfigObject();
            }

            $this->data[$keys[0]]->set($keys[1], $value);

            return $this;
        }

        if (!array_key_exists($name, $this->data)) {
            $this->data[$name] = new ConfigObject();
        }

        $this->data[$name] = $value;

        return $this;
    }

    /**
     * ConfigObject is an object representing config data in an OO way
     *
     * @param  array|ArrayObject|AbstractDriver $resource Config resource
     *
     * @throws ConfigException
     */
    public function __construct($resource = [])
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
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->data);
    }

    /**
     * @inheritdoc
     */
    public function offsetGet($offset)
    {
        return $this->data[$offset];
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value)
    {
        $this->data[$offset] = $value;
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
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
        return isset($this->data[$name]);
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
        if (isset($this->data[$name])) {
            unset($this->data[$name]);
        }
    }

    public function __toString()
    {
        return '' . var_export($this->toArray(), true);
    }

    /**
     * @inheritdoc
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->data);
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
     * Merge current config with another config
     *
     * @param array|ArrayObject|ConfigObject $config ConfigObject or array to merge with
     *
     * @throws ConfigException
     * @return $this
     */
    public function mergeWith($config)
    {
        if ($this->isArray($config) || $this->isArrayObject($config)) {
            $config = Config::getInstance()->php(StdObjectWrapper::toArray($config));
        }

        foreach ($config as $key => $value) {
            if (array_key_exists($key, $this->data)) {
                if (is_numeric($key)) {
                    $this->data[] = $value;
                    continue;
                } elseif ($value instanceof ConfigObject && $this->data[$key] instanceof ConfigObject) {
                    $this->data[$key]->mergeWith($value);
                    continue;
                }
            }
            $this->data[$key] = $value instanceof ConfigObject ? new ConfigObject($value->toArray(), false) : $value;
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
        $this->data = [];
        $array = StdObjectWrapper::toArray($config);
        foreach ($array as $key => $value) {
            if ($this->isArray($value)) {
                $this->data[$key] = new static($value, false);
            } else {
                if (!isset($this->data[$key])) {
                    $this->data[$key] = $value;
                }
            }
        }
    }

    public function serialize()
    {
        $data = [
            'data'         => [],
            'resourceType' => $this->resourceType,
            'driverClass'  => $this->driverClass
        ];
        $data['data'] = $this->data;

        return serialize($data);
    }

    public function unserialize($string)
    {
        $data = unserialize($string);
        $this->driverClass = $data['driverClass'];
        $this->resourceType = $data['resourceType'];
        $this->data = $data['data'];
    }
}
