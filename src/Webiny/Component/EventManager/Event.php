<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\EventManager;

use Webiny\Component\StdLib\StdLibTrait;


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
                    ]
                );
            }
            $this->eventData = $this->arr($eventData);
        } else {
            $this->eventData = $this->arr();
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
        if ($this->eventData->keyExists($name)) {
            return $this->eventData->key($name);
        }

        return $default;
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
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param mixed $offset <p>
     *                      An offset to check for.
     *                      </p>
     *
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     *       The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset)
    {
        return $this->eventData->keyExists($offset);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param mixed $offset <p>
     *                      The offset to retrieve.
     *                      </p>
     *
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        return $this->eventData->key($offset);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param mixed $offset <p>
     *                      The offset to assign the value to.
     *                      </p>
     * @param mixed $value  <p>
     *                      The value to set.
     *                      </p>
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->eventData->key($offset, $value);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param mixed $offset
     * @param mixed $offset <p>
     *                      The offset to unset.
     *                      </p>
     *
     * @return void
     */
    public function offsetUnset($offset)
    {
        $this->eventData->removeKey($offset);
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
        return $this->eventData->keyExists($name);
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
        if ($this->eventData->keyExists($name)) {
            $this->eventData->removeKey($name);
        }
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     */
    public function getIterator()
    {
        return $this->eventData->getIterator();
    }

    /**
     * Get event data in form of an array
     * @return array Event data array
     */
    public function toArray()
    {
        $data = [];
        foreach ($this->eventData as $k => $v) {
            $data[$k] = $v;
        }

        return $data;
    }
}