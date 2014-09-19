<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\StdLib\StdObject;

use Webiny\Component\StdLib\Config\ConfigAbstract;
use Webiny\Component\StdLib\StdObject\ArrayObject\ArrayObject;
use Webiny\Component\StdLib\StdObject\StringObject\StringObject;
use Webiny\Component\StdLib\ValidatorTrait;

/**
 * Standard object abstract class.
 * Extend this class when you want to create your own standard object.
 *
 * @package         Webiny\Component\StdLib\StdObject
 */
abstract class StdObjectAbstract implements StdObjectInterface
{
    use ValidatorTrait;

    /**
     * ArrayObject that caches the names of standard objects. This is used by StdObjectAbstract::_getStdObjectName
     * @var ArrayObject
     */
    private static $_stdObjectName = null;

    /**
     * Return, or update, current standard objects value.
     *
     * @param null $value If $value is set, value is updated and ArrayObject is returned.
     *
     * @return mixed
     */
    public function val($value = null)
    {
        if (!$this->isNull($value)) {
            $this->_value = $value;

            return $this;
        }

        return $this->_value;
    }

    /**
     * Returns an instance to current object.
     *
     * @return $this
     */
    protected function _getObject()
    {
        return $this;
    }

    /**
     * Throw a standard object exception.
     *
     * @param $message
     *
     * @return StdObjectException
     */
    public function exception($message)
    {
        return new StdObjectException($message);
    }

    /**
     * Returns the name of standard object config class, or throws a StdObjectException if config class does not exist.
     *
     * @return string
     * @throws StdObjectException
     */
    private static function _getStdObjectConfigClassName()
    {
        $stdObjectName = self::_getStdObjectName();
        $configClassName = 'Webiny\Component\StdLib\StdObject\\' . $stdObjectName . '\\' . $stdObjectName . 'Config';
        if (!self::classExists($configClassName)) {
            throw new StdObjectException('StdObjectAbstract: Config class for "' . $stdObjectName . '" standard object does not exist.'
            );
        }

        return $configClassName;
    }

    /**
     * Returns the name of current standard object without its namespace.
     *
     * @return ArrayObject|StringObject
     */
    private static function _getStdObjectName()
    {
        // check if self::$_stdObjectName is created
        if (self::isNull(self::$_stdObjectName)) {
            self::$_stdObjectName = new ArrayObject([]);
        }

        // get called class name (with full namespace)
        $cc = get_called_class();

        // check if we already have an entry for this class name
        if (!self::$_stdObjectName->keyExists($cc)) {
            $str = new StringObject($cc);
            $className = $str->explode('\\')->last();
            self::$_stdObjectName->key($cc, $className);
        } else {
            $className = self::$_stdObjectName->key($cc);
        }

        return $className;
    }
}