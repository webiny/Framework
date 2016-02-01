<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\EventManager;

use Webiny\Component\StdLib\SingletonTrait;
use Webiny\Component\StdLib\StdLibTrait;
use Webiny\Component\StdLib\StdObject\ArrayObject\ArrayObject;

/**
 * EventManager is responsible for handling events. It is the main class for subscribing to events and firing events.<br />
 * Besides regular event names, it supports firing of wildcard events, ex: 'webiny.*' will fire all events starting with 'webiny.'
 *
 * @package         Webiny\Component\EventManager
 */
class EventManager
{
    use StdLibTrait, SingletonTrait;

    /**
     * Suppress EventManager events
     * @var bool
     */
    private $suppressEvents = false;

    /**
     * Registered events and event subscribers
     * @var ArrayObject
     */
    private $events;

    /**
     * @var EventProcessor
     */
    private $eventProcessor;

    /**
     * Subscribe to event
     *
     * @param string        $eventName Event name you want to listen
     * @param EventListener $eventListener (Optional) If specified, given EventListener will be used for this event
     *
     * @throws EventManagerException
     * @return EventListener Return instance of EventListener
     */
    public function listen($eventName, EventListener $eventListener = null)
    {

        if (!$this->isString($eventName) || $this->str($eventName)->length() == 0) {
            throw new EventManagerException(EventManagerException::INVALID_EVENT_NAME);
        }

        if ($this->isNull($eventListener)) {
            $eventListener = new EventListener();
        }

        $eventListeners = $this->events->key($eventName, [], true);
        $eventListeners[] = $eventListener;
        $this->events->key($eventName, $eventListeners);

        return $eventListener;
    }

    /**
     * Subscribe to events using event subscriber
     *
     * @param EventSubscriberInterface $subscriber Subscriber class
     *
     * @return $this Return instance of EventManager
     */
    public function subscribe(EventSubscriberInterface $subscriber)
    {
        $subscriber->subscribe();

        return $this;
    }

    /**
     * Fire event
     *
     * @param string      $eventName Event to fire. You can also use wildcards to fire multiple events at once, ex: 'event.*'
     * @param array|Event $data Array or Event object
     * @param null        $resultType If specified, the event results will be filtered using given class/interface name
     * @param null|int    $limit Number of results to return
     *
     * @return array Array of results from EventListeners
     */
    public function fire($eventName, $data = null, $resultType = null, $limit = null)
    {

        if ($this->suppressEvents) {
            return null;
        }

        if ($this->str($eventName)->endsWith('*')) {
            return $this->fireWildcardEvents($eventName, $data, $resultType);
        }

        if (!$this->events->keyExists($eventName)) {
            return null;
        }

        $eventListeners = $this->events->key($eventName);
        if (!$this->isInstanceOf($data, '\Webiny\Component\EventManager\Event')) {
            $data = new Event($data);
        }

        return $this->eventProcessor->process($eventListeners, $data, $resultType, $limit);
    }

    /**
     * Enable event handling
     *
     * After calling this method EventManager will process all fired events.
     *
     * @return $this
     */
    public function enable()
    {
        $this->suppressEvents = false;

        return $this;
    }

    /**
     * Disable event handling
     *
     * After calling this method EventManager will ignore all fired events.
     *
     * @return $this
     */
    public function disable()
    {
        $this->suppressEvents = true;

        return $this;
    }

    /**
     * Get array of event listeners
     *
     * @param $eventName
     *
     * @return array
     */
    public function getEventListeners($eventName)
    {
        return $this->events->key($eventName, [], true);
    }

    /**
     * Singleton constructor
     */
    protected function init()
    {
        $this->events = $this->arr();
        $this->eventProcessor = EventProcessor::getInstance();
    }

    /**
     * Process events starting with given prefix (ex: webiny.* will process all events starting with 'webiny.')
     *
     * @param $eventName
     * @param $data
     * @param $resultType
     *
     * @return null|array
     */
    private function fireWildcardEvents($eventName, $data, $resultType)
    {
        // Get event prefix
        $eventPrefix = $this->str($eventName)->subString(0, -1)->val();
        // Find events starting with the prefix
        $events = [];
        foreach ($this->events as $eventName => $eventListeners) {
            if ($this->str($eventName)->startsWith($eventPrefix)) {
                $events[$eventName] = $eventListeners;
            }
        }

        if ($this->arr($events)->count() > 0) {
            if (!$this->isInstanceOf($data, '\Webiny\Component\EventManager\Event')) {
                $data = new Event($data);
            }

            $results = $this->arr();
            foreach ($events as $eventListeners) {
                $result = $this->eventProcessor->process($eventListeners, $data, $resultType);
                $results->merge($result);
            }

            return $results->val();
        }

        return null;
    }
}