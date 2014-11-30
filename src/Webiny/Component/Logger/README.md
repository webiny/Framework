Logger
======

Logger component is used to log data. You can log whatever you want to whatever destination, all you need to do is create a `handler` for your logger driver.
This component was heavily inspired by https://github.com/Seldaek/monolog library (if you used that one before, this component will be very familiar to you).
You may say it's identical, but we found some things we wanted to change/improve, like having a `Record` class with proper getters/setters,
we changed some method namings, processing logic and other bits and pieces to better suite our framework.
We do lack different handlers, formatters and processors out-of-the-box, but that will come with time.

Install the component
---------------------
The best way to install the component is using Composer.

```json
{
    "require": {
        "webiny/logger": "1.1.*"
    }
}
```
For additional versions of the package, visit the [Packagist page](https://packagist.org/packages/webiny/logger).

Once you have your `composer.json` file in place, just run the install command.

    $ php composer.phar install

To learn more about Composer, and how to use it, please visit [this link](https://getcomposer.org/doc/01-basic-usage.md).

Alternatively, you can also do a `git checkout` of the repo.

## Drivers
Built-in drivers are:
- `Null`
- `Webiny`

`Null` driver is used to turn off logging without changing your code. You simply change your configuration to use `Null` driver and all your logging calls will be sent into the void.
`Webiny` driver contains most of the functionality you will ever need from a logger, so that driver is used by default.

Logger component uses PSR-3 Logger Interface and PSR-3 Log Levels. All log level constants are located in `LoggerLevel` class.
With that being said, you can create your own logger driver, which will not even have handlers, formatters, processors, or anything like that.
Just implement the logger interface and the rest is up to you. If you do like Monolog concept, then `Webiny` driver is the way to go.

## Logger setup
Here is an example logger service setup, which uses a `FileHandler`, two processors and a `FileFormatter`.
The structure of the config file is identical to all other Webiny components that register services.

```yaml
Logger:
    Parameters:
        Logger.Class: \Webiny\Component\Logger\Logger
        Logger.Driver.Class: \Webiny\Component\Logger\Driver\Webiny
        Logger.Processor.FileLine.Class: \Webiny\Component\Logger\Driver\Webiny\Processor\FileLineProcessor
        Logger.Processor.MemoryUsage.Class: \Webiny\Component\Logger\Driver\Webiny\Processor\MemoryUsageProcessor
        Logger.Formatter.File.Class: \Webiny\Component\Logger\Driver\Webiny\Formatter\FileFormatter
        Logger.Handlers.File.Class: \Webiny\Component\Logger\Driver\Webiny\Handler\FileHandler
        Logger.LogFile: /var/log/WebinyFileLogger.log
    Services:
        MyFileLogger:
            Class: %Logger.Class%
            Arguments: [System, %Logger.Driver.Class%]
            Calls:
                - [addHandler, [@Logger.LogHandler]]
        LogHandler:
            Class: %Logger.Handlers.File.Class%
            Arguments: [%Logger.LogFile%, [], true, false]
            Calls:
                - [addProcessor, [%Logger.Processor.FileLine.Class%]]
                - [addProcessor, [%Logger.Processor.MemoryUsage.Class%]]
                - [setFormatter, [%Logger.Formatter.File.Class%]]
    Configs:
        Formatter:
            Default:
                DateFormat: 'H:i:s d-m-Y'
            File:
                RecordFormat: '%datetime% [%loggerName%] [%level%]: %message%\nContext: %context%\nExtra: %extra%\n\n'
    ClassLoader:
        Psr: '../Psr'
```

To use your logger in PHP:

```php
// Set component config
Logger::setConfig($pathToYourConfigFile);

// Now your logger exists in the system as a 'MyFileLogger' service.
// You can use it by either using a LoggerTrait...
$logger = $this->logger('MyFileLogger');
$logger->info('This is pretty simple!');

// ... or by using ServiceManagerTrait. Note that ServiceManager groups services by component
// So your service is called 'Logger.MyFileLogger'. When using LoggerTrait, it appends the service group for you.
$logger = $this->service('Logger.MyFileLogger');
$logger->warn('This is just a little bit longer...');
```

## The logic behind logger setup and log message processing
1. Create a logger instance
2. Create a handler instance(s) (you can have multiple handlers in your logger)
3. Add processors and formatters to your handler instance(s)
4. Add handler(s) to your logger
5. Use your logger

When you log a message, it goes through each handler, which in turn passes it to each processor. Formatter is the last thing to change a record.
It simply formats the record into form that is appropriate for your handler. It may be string, array, etc.
At the very end of processing - handler writes the formatted record to destination (be it file, database, email, socket, etc.)

Resources
---------

To run unit tests, you need to use the following command:

    $ cd path/to/Webiny/Component/Logger/
    $ composer.phar install
    $ phpunit