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

    /**
     * Config data
     *
     * @var ArrayObject
     */
    protected $data;

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
        $name = $this->str($name);
        if ($name->contains('.')) {
            $keys = $name->trim('.')->explode('.', 2);

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
        $name = $this->str($name);
        if ($name->contains('.')) {
            $keys = $name->trim('.')->explode('.', 2);

            if (!$this->data->keyExists($keys[0])) {
                $this->data[$keys[0]] = new ConfigObject();
            }

            $this->data[$keys[0]]->set($keys[1], $value);

            return $this;
        }

        $this->data->key($name, $value);

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
        // Validate given resources
        if (!$this->isArray($resource) && !$this->isInstanceOf($resource, AbstractDriver::class) && !$this->isArrayObject($resource)) {
            throw new ConfigException("ConfigObject resource must be a valid array, " . ArrayObject::class . " or " . AbstractDriver::class);
        }


        if ($this->isInstanceOf($resource, AbstractDriver::class)) {
            // Store driver class name
            $this->driverClass = get_class($resource);
            // Get driver to parse resource and return data array
            $resource = $resource->getArray();
        }

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
            $data[$k] = $this->isInstanceOf($v, $this) ? $v->toArray() : $v;
        }

        return $asArrayObject ? $this->arr($data) : $data;
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
        return '' . var_export($this->toArray(), true);
    }

    /**
     * @inheritdoc
     */
    public function getIterator()
    {
        return $this->data->getIterator();
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
            $config = Config::getInstance()->php(StdObjectWrapper::toArray($config));
        }

        foreach ($config as $key => $value) {
            if ($this->data->keyExists($key)) {
                if (is_numeric($key)) {
                    $this->data[$key] = $value;
                    continue;
                } elseif ($value instanceof ConfigObject && $this->data[$key] instanceof ConfigObject) {
                    $this->data[$key]->mergeWith($value);
                    continue;
                }
            }
            $this->data[$key] = $value instanceof ConfigObject ? new ConfigObject($value->toArray()) : $value;
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
        foreach ($config as $key => $value) {
            $this->data[$key] = $this->isArray($value) ? new static($value) : $value;
        }
    }

    public function serialize()
    {
        return serialize([
            'data'        => $this->data,
            'driverClass' => $this->driverClass
        ]);
    }

    public function unserialize($string)
    {
        $data = unserialize($string);
        $this->driverClass = $data['driverClass'];
        $this->data = new ArrayObject($data['data']);
    }
}
