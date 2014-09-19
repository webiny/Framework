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
 * EventListener is a class that holds event handler information.
 * A new EventListener is created each time you subscribe to an event.
 *
 * @package         Webiny\Component\EventManager
 */
class EventListener
{
    use StdLibTrait;

    private $_handler = null;
    private $_method = 'handle';
    private $_priority = 101;

    /**
     * Set handler for event. Can be a callable, class name or class instance.
     *
     * @param $handler
     *
     * @throws EventManagerException
     * @return $this
     */
    public function handler($handler)
    {

        if ($this->isNumber($handler) || $this->isBoolean($handler) || $this->isEmpty($handler)) {
            throw new EventManagerException(EventManagerException::INVALID_EVENT_HANDLER);
        }

        if ($this->isString($handler) || $this->isStringObject($handler)) {
            $handler = StdObjectWrapper::toString($handler);
            if (!class_exists($handler)) {
                throw new EventManagerException(EventManagerException::INVALID_EVENT_HANDLER);
            }
            $handler = new $handler;
        }
        $this->_handler = $handler;

        return $this;
    }

    /**
     * Set listener priority between 101 and 999.<br />
     * Default priority is 101.<br />
     * Bigger values mean higher listener priority, and will be executed first.
     *
     * @param $priority
     *
     * @return $this
     * @throws EventManagerException
     */
    public function priority($priority)
    {
        if (!$this->isNumber($priority)) {
            throw new EventManagerException(EventManagerException::MSG_INVALID_ARG, [
                    '$priority',
                    'integer'
                ]
            );
        }

        if ($priority <= 100 || $priority >= 1000) {
            throw new EventManagerException(EventManagerException::INVALID_PRIORITY_VALUE);
        }
        $this->_priority = $priority;

        return $this;
    }

    /**
     * Set method to be called on handler.<br />
     * If not set, default method will be called:
     * <code>handle(Event $event)</code>
     *
     * @param string $method Method to call on handler
     *
     * @throws EventManagerException
     * @return $this
     */
    public function method($method)
    {
        if (!$this->isString($method) && !$this->isStringObject($method)) {
            throw new EventManagerException(EventManagerException::MSG_INVALID_ARG, [
                    '$method',
                    'string|StringObject'
                ]
            );
        }
        $this->_method = StdObjectWrapper::toString($method);

        return $this;
    }

    /**
     * Get handler object
     * @return mixed
     */
    public function getHandler()
    {
        return $this->_handler;
    }

    /**
     * Get handler method
     * @return string
     */
    public function getMethod()
    {
        return $this->_method;
    }

    /**
     * Get listener priority
     * @return int
     */
    public function getPriority()
    {
        return $this->_priority;
    }


}