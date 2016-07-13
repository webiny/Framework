<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Entity\Attribute;

use Webiny\Component\Entity\AbstractEntity;
use Webiny\Component\Entity\EntityCollection;

/**
 * DynamicAttribute
 * @package Webiny\Component\Entity\AttributeType
 */
class DynamicAttribute extends AbstractAttribute
{

    protected $storeToDb = false;
    protected $storedValue = null;
    protected $callable = null;
    protected $defaultParams = [];

    /**
     * @param string         $name
     * @param AbstractEntity $parent
     * @param callable       $callable
     */
    public function __construct($name = null, AbstractEntity $parent = null, $callable = null)
    {
        $this->callable = is_string($callable) ? [$parent, $callable] : $callable;

        if (is_string($callable)) {
            $rfc = new \ReflectionClass($parent);
            $params = $rfc->getMethod($callable)->getParameters();
        } else {
            $rf = new \ReflectionFunction($callable);
            $params = $rf->getParameters();
        }

        if ($params) {
            /* @var $p \ReflectionParameter */
            foreach ($params as $p) {
                if($p->isDefaultValueAvailable()){
                    $this->defaultParams[] = $p->getDefaultValue();
                }
            }
        }

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
        if ($value instanceof AbstractEntity) {
            return $this->processToDbValue($value->id);
        }

        return parent::getDbValue();
    }

    /**
     * @inheritDoc
     */
    public function toArray($fields = [], $params = [])
    {
        $value = $this->processToArrayValue($this->getValue($params));
        if ($value instanceof AbstractEntity || $value instanceof EntityCollection) {
            $value = $value->toArray($fields);
        }

        return $value;
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

        foreach ($this->defaultParams as $i => $defaultValue) {
            if (!isset($arguments[$i])) {
                $arguments[$i] = $defaultValue;
            }
        }

        $arguments[] = $this->storedValue;

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
            $this->storedValue = $this->processFromDbValue($value);

            return $this;
        }

        $currentValue = $this->getValue();
        if ($currentValue instanceof AbstractEntity && is_array($value)) {
            $currentValue->populate($value);
        }

        return $this;
    }


    /**
     * Perform validation against given value
     *
     * @param $value
     *
     * @return $this
     */
    protected function validate(&$value)
    {
        return $this;
    }

}