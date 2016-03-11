<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Entity\Attribute;

use Webiny\Component\Entity\EntityAbstract;
use Webiny\Component\Entity\EntityValidationException;

/**
 * DynamicAttribute
 * @package Webiny\Component\Entity\AttributeType
 */
class DynamicAttribute extends AttributeAbstract
{

    protected $storeToDb = false;
    protected $storedValue = null;
    protected $callable = null;

    /**
     * @param string         $name
     * @param EntityAbstract $parent
     * @param callable       $callable
     */
    public function __construct($name = null, EntityAbstract $parent = null, $callable = null)
    {
        $this->callable = $callable;
        parent::__construct($name, $parent);
    }

    /**
     * Set dynamic attribute function
     *
     * @param $callable
     *
     * @return $this
     */
    public function setCallable($callable)
    {
        $this->callable = $callable;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getDbValue()
    {
        $value = $this->getValue();
        if ($value instanceof EntityAbstract) {
            return $this->processToDbValue($value->id);
        }

        return parent::getDbValue();
    }


    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return $this->processToArrayValue($this->getValue());
    }


    public function setStoreToDb()
    {
        $this->storeToDb = true;

        return $this;
    }

    /**
     * Get attribute value
     *
     * @param array $arguments
     *
     * @return $this
     */
    public function getValue($arguments = [])
    {
        $callable = $this->callable;
        if (is_string($callable)) {
            $callable = [$this->parent, $callable];
        }

        return $this->processGetValue(call_user_func_array($callable, $arguments));
    }

    /**
     * Set attribute value
     *
     * @param null $value
     * @param bool $fromDb
     *
     * @return $this
     */
    public function setValue($value = null, $fromDb = false)
    {
        if ($fromDb) {
            $this->storedValue = $value;
        }

        return $this;
    }


    /**
     * Perform validation against given value
     *
     * @param $value
     *
     * @throws EntityValidationException
     * @return $this
     */
    protected function validate(&$value)
    {
        return $this;
    }

}