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
}