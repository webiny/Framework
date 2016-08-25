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

        $rf = new \ReflectionFunction($callable);
        $params = $rf->getParameters();

        if ($params) {
            /* @var $p \ReflectionParameter */
            foreach ($params as $p) {
                if ($p->isDefaultValueAvailable()) {
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
     * @param array $params
     * @param bool  $processCallbacks Process `onGet` callbacks
     *
     * @return $this
     */
    public function getValue($params = [], $processCallbacks = true)
    {
        foreach ($this->defaultParams as $i => $defaultValue) {
            if (!isset($params[$i])) {
                $params[$i] = $defaultValue;
            }
        }

        // In case the value of this dynamic attribute is stored to DB, we pass the stored value as the last parameter to the function
        $params[] = $this->storedValue;
        $value = call_user_func_array($this->callable, $params);

        return $this->processGetValue($value, [], $processCallbacks);
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