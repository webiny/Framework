Event Manager Component
===============

`EventManager` allows you to easily manage events throughout your application.

Install the component
---------------------
The best way to install the component is using Composer.

```bash
composer require webiny/event-manager
```
For additional versions of the package, visit the [Packagist page](https://packagist.org/packages/webiny/event-manager).

## Usage

Accessing `EventManager` can be done in 2 ways. The preferable way is using `EventManagerTrait`, but you can also access it directly, using `EventManager::getInstance()`. Let's see a simple example of subscribing to an event called `some.event` with an instance of `YourHandler`:

```php
class YourClass{
    use EventManagerTrait;
    
    public function index(){
        $this->eventManager()->listen('some.event')->handler(new YourHandler());
    }
}

```
You're done! You have just subscribed to an event and the moment `some.event` is fired, your handler will process it.

## Event handlers

Now let's take a look at `YourHandler`. An event handler can be any class. `EventManager` will call `handle(Event $event)` method on your handler object, by default. You can, however, specify a method you want `EventManager` to call:

```php
class YourHandler{

    public function customHandle(Event $event){
        // Do something with the $event...
    }
    
}

// Using your custom method
$this->eventManager()->listen('some.event')->handler(new YourHandler())->method('customHandle');
```

Besides using classes, you can also respond to an event using a callable:

```
// Using callable as event handler

$handler = function(Event $event){
    // Do something with the $event...
};

$this->eventManager()->listen('some.event')->handler($handler);
```

## Firing events
To fire a simple event use the following code:

```php
$this->eventManager()->fire('some.event');
```
You can also pass some data when firing an event, which will be passed to every event listener:

```php
$data = [
    'some' => 'data',
    'ip' => '192.168.1.10'
];

$this->eventManager()->fire('some.event', $data);
```

Any given data that is not an `Event` object, will be converted to generic `Event` object and your data will be accessible either by using array keys, or as object properties:

```php
class YourHandler{

    public function customHandle(Event $event){
        // Access your data 
        echo $event->some; // 'data'
        echo $event['some'] // 'data'
    }
    
}
```

If you want to use custom `Event` data types, refer to section [Custom event classes](#custom-event-classes)

## Firing events using a wildcard
You can also use wildcard to fire multiple events at once. The following code will fire all events starting with `event.` and pass `$data` to each one of them:
```php
$this->eventManager()->fire('event.*', $data);
```

## Execution priority
`EventManager` allows you to specify an execution priority using `priority()` method. Here's an example:

```php
// Specify a priority of execution for your event listeners
$this->eventManager()->listen('some.event')->handler(new YourHandler())->method('customHandler')->priority(250);
$this->eventManager()->listen('some.event')->handler(new YourHandler())->method('secondCustomHandler')->priority(400);
$this->eventManager()->listen('some.event')->handler(new YourHandler())->method('thirdCustomHandler');

// Now let's fire an event
$this->eventManager()->fire('some.event');
```

After firing an event, the event listeners will be ordered by priority in descending order. The higher the priority, the sooner the listener will be executed. In this example, the order of execution will be as follows: `secondCustomHandler`, `customHandler`, `thirdCustomHandler`. Default priority is `101`, so `thirdCustomHandler` is executed last.

## Custom event classes

When firing events, you can also pass your own event classes, that extend generic `Event` class. For example, you want to fire an event called `cms.page_saved` and pass the `Page` object. Of course, you could simply pass an array like `['page' => $pageObject]`, but for the sake of the example, let's pretend it's more complicated than that:

```php
// Create your `PageEvent` class

class PageEvent extends Event{

    private $_page;

    public function __construct(Page $page){
        // Call constructor of parent Event class
        parent::__construct();
        
        // Set your page object
        $this->_page = $page;
    }

    public function getPage(){
        return $this->_page;
    }
    
}

// Fire an event

$pageEvent = new PageEvent($pageObject);

$this->eventManager()->fire('cms.page_saved', $pageEvent);

// In your handler, you can now access page object using $event->getPage()

class YourHandler{

    public function customHandle(PageEvent $event){
        $pageObject = $event->getPage();
    }
    
}

```

This is a simple example, but it shows the power of creating your own `Event` classes and add as much functionality to your events as you need.

## Event subscriber

Another cool feature of the `EventManager` is the ability to subscribe to multiple events at once. You will need to create a subscriber class implementing `EventSubscriberInterface`:

```php
class PageEventSubscriber implements EventSubscriberInterface {
    use EventManagerTrait;

    /**
     * Handle page creation event
     */
    public function onPageCreated($event)
    {
        //
    }

    /**
     * Handle page update
     */
    public function onPageUpdated($event)
    {
        //
    }

    /**
     * Register the listeners for the subscriber.
     */
    public function subscribe()
    {
        $this->eventManager()->listen('cms.page_created')->handler($this)->method('onPageCreated');
        $this->eventManager()->listen('cms.page_updated')->handler($this)->method('onPageUpdated');
    }

}

// Subscriber to multiple events using your new subscriber class
$this->eventManager()->subscribe($subscriber);
```

There are situations when you need to temporarily disable EventManager. For example, deleting a huge portion of files that are not directly related to the application (local cache files) does not require firing all of related events. In this case use the following methods:

```php
// Disabling EventManager
$this->eventManager()->disable();

// Do some work that would fire loads of unnecessary events...

// Enabling EventManager
$this->eventManager()->enable();

```

Resources
---------

To run unit tests, you need to use the following command:

    $ cd path/to/Webiny/Component/EventManager/
    $ composer.phar install
    $ phpunit