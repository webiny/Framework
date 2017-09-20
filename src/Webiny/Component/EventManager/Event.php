<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\EventManager;

use Webiny\Component\StdLib\StdLibTrait;
use Webiny\Component\StdLib\StdObject\StdObjectWrapper;


/**
 * Event class holds event data. Data can be accessed using array keys or as object properties.
 * Each time an event is fired, an instance of Event class is passed to handlers.
 * By extending this class you can implement your own event class and expand it with whatever functionality you might need.
 *
 * @package         Webiny\Component\EventManager
 */
class Event implements \ArrayAccess, \IteratorAggregate
{
    use StdLibTrait;

    private $propagationStopped = false;
    private $eventData;

    public function __construct($eventData = null)
    {
        if (!$this->isNull($eventData)) {
            if (!$this->isArray($eventData) && !$this->isArrayObject($eventData)) {
                throw new EventManagerException(EventManagerException::MSG_INVALID_ARG, [
                    '$eventData',
                    'array|ArrayObject'
                ]);
            }
            $this->eventData = StdObjectWrapper::toArray($eventData);
        } else {
            $this->eventData = [];
        }
    }

    /**
     * Check if propagation for this event is stopped
     * @return bool
     */
    public function isPropagationStopped()
    {
        return $this->propagationStopped;
    }

    /**
     * Stops the propagation of the event to further event listeners.
     *
     * After stopPropagation() is called, no other listeners will be processed.
     *
     * @return void
     */
    public function stopPropagation()
    {
        $this->propagationStopped = true;
    }

    /**
     * Get value or return $default if there is no element set.
     *
     * @param  string $name
     * @param  mixed  $default
     *
     * @return mixed Config value or default value
     */
    public function get($name, $default = null)
    {
        if (array_key_exists($name, $this->eventData)) {
            return $this->eventData[$name];
        }

        return $default;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->eventData;
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
            $this->eventData[] = $value;
        } else {
            $this->eventData[$name] = $value;
        }
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->eventData);
    }

    /**
     * @inheritdoc
     */
    public function offsetGet($offset)
    {
        return $this->eventData[$offset];
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value)
    {
        $this->eventData[$offset] = $value;
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset)
    {
        unset($this->eventData[$offset]);
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
        return isset($this->eventData[$name]);
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
        if (array_key_exists($name, $this->eventData)) {
            unset($this->eventData[$name]);
        }
    }

    /**
     * @inheritdoc
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->eventData);
    }
}