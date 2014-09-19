<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\EventManager\Tests;

use Webiny\Component\EventManager\Event;
use Webiny\Component\EventManager\EventManager;

class EventManagerTest extends \PHPUnit_Framework_TestCase
{
    public function testEvenManager()
    {
        // Create event listener
        $callback = function (Event $event) {
            return $event->parameter * 10;
        };

        $secondCallback = function (Event $event) {
            return $event->parameter * 20;
        };

        // This listener should be executed last
        EventManager::getInstance()->listen('test.event')->handler($callback)->priority(200);
        // This listener should be executed first
        EventManager::getInstance()->listen('test.event')->handler($secondCallback)->priority(400);
        $results = EventManager::getInstance()->fire('test.event', ['parameter' => 2]);
        // Event results are returned in form of an array
        $this->assertEquals(40, $results[0]);
        $this->assertEquals(20, $results[1]);
    }

}