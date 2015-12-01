<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Entity\Attribute;

use Webiny\Component\Entity\EntityAbstract;
use Webiny\Component\Entity\Validation\ValidationException;

/**
 * DynamicAttribute
 * @package Webiny\Component\Entity\AttributeType
 */
class DynamicAttribute extends AttributeAbstract
{

    protected $storeToDb = false;
    protected $storedValue = null;

    /**
     * @param string         $attribute
     * @param EntityAbstract $entity
     * @param callable       $callable
     */
    public function __construct($attribute, EntityAbstract $entity, $callable)
    {
        $this->callable = $callable;
        parent::__construct($attribute, $entity);
    }

    /**
     * @inheritDoc
     */
    public function getDbValue()
    {
        $value = $this->getValue();
        if($value instanceof EntityAbstract){
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
     * @return $this
     */
    public function getValue()
    {
        $callable = $this->callable;
        if (is_string($callable)) {
            $callable = [$this->entity, $callable];
        }

        return $this->processGetValue(call_user_func_array($callable, [$this->storedValue]));
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
     * @throws ValidationException
     * @return $this
     */
    protected function validate(&$value)
    {
        return $this;
    }

}