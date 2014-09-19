Service Manager
===============

Install the component
---------------------
The best way to install the component is using Composer.

```json
{
    "require": {
        "webiny/service-manager": "1.0.*"
    }
}
```
For additional versions of the package, visit the [Packagist page](https://packagist.org/packages/webiny/service-manager).

Once you have your `composer.json` file in place, just run the install command.

    $ php composer.phar install

To learn more about Composer, and how to use it, please visit [this link](https://getcomposer.org/doc/01-basic-usage.md).

Alternatively, you can also do a `git checkout` of the repo.

## Usage

Available service configuration parameters are:

* `Class`
* `Arguments` (`Object` & `ObjectArguments`)
* `Abstract`
* `Calls`
* `Scope`

Extra parameters, for factory services, are:

* `Factory` - class or service
* `Static` (Optional) - defaults to TRUE, means that `Method` will be called statically on `Factory` object
* `Method` - a method to call on `Factory` object
* `MethodArguments` (Optional) - method arguments for `Method`

There are 2 possible types of scope:

* `container` (Default) - only 1 instance of service is created, and is re-used on each subsequent request of service
* `prototype` - a new instance of service is created each time a service is requested

## Service definition

To register a service you need to create a service config object (using `ConfigObject` class) and pass it to `ServiceManager`

You can either create your configuration files in YAML or use plain PHP and build arrays straight from your code.
If using PHP - call `new ConfigObject($configArray)` to build a proper config object.
If using YAML - call `$config = Config::getInstance()->yaml($pathToYourConfigFile);`

Basic service definition takes only `Class` parameter:

```php
$config = [
    'Class' => '\My\Service\Class'
];

ServiceManager::getInstance()->registerService('MyService', new ConfigObject($config));

// Now get your service
$myService = ServiceManager::getInstance()->getService('MyService');

```

You can group your services in logic groups, by creating an array of service definitions and then registering them as a group:

```php
$config = [
    'MyLogger' => [
        'Class' => '\My\Service\Class'
    ],
    'MyMailer' => [
        'Class' => '\My\Mailer\Class'
    ]
];

ServiceManager::getInstance()->registerServices('MyServiceGroup', new ConfigObject($config));

// Now get your specific service
$myMailer = ServiceManager::getInstance()->getService('MyServiceGroup.MyMailer');

```


## Constructor arguments

You can provide constructor arguments to your service class, by using `Arguments` parameter. Argument can be any value, including class name (it will be instantiated and passed to constructor as a PHP object) and a reference to another service (enter service reference using `@` character):

```php
$config = [
    'Class' => '\My\Service\Class'
    'Arguments' => ['FirstArgument', [1,2,3], '\This\Class\Will\Be\Instantiated', '@someOtherService']
];
```

In case you need to provide constructor parameters to your argument class or service, you will need to use an extended arguments syntax (here is a config example written in YAML):

```yaml
Class: \My\Service\Class
Arguments:
    name: FirstArgument
    ids: [1,2,3]
    some_instance:
        Object: \This\Class\Will\Be\Instantiated
        ObjectArguments: [Name, Y-m-d]
    some_service:
        Object: @some.other.service
        ObjectArguments: [Name]
```

## Creating service from a YAML config file

To create a `ConfigObject` out of your YAML file and register a new service, simply call:

```php
$config = Config::getInstance()->yaml($pathToYourConfigFile);
ServiceManager::getInstance()->registerService('MyNewService', $config);
```

## Service object method calls

In case you need to call some methods on your service instance, you can specify them using `Calls` parameter:

```yaml
Class: \My\Service\Class
Arguments: [FirstArgument, [1,2,3], \This\Class\Will\Be\Instantiated, @some.other.service]
Calls:
    - [yourMethod]
    - [yourMethodWithArguments, [Arg1, 123]]
    - [yourMethodWithClassArgument, [\Some\Class\That\Will\Be\Instantiated]]
    - [yourMethodWithServiceArgument, [@some_service]]
```

## Abstract services and parameters
Service manager also supports abstract services. When you have 2 or more services sharing similar functionality, you can extract common stuff into an abstract service. In the following example we also use `Parameters`. Parameters are like variables, define them once, and reuse whenever you need them.

Parameters config file:

```yaml
# Definition of parameters
logger.class: \Webiny\Component\Logger\Logger
logger.driver.class: \Webiny\Component\Logger\Drivers\Webiny
logger.handler.udp.class: \Webiny\Component\Logger\Drivers\Webiny\Handlers\UDPHandler
```

Services config file (this can also be defined in plain PHP array):

```yaml
UdpHandler:
    Class: %logger.handler.udp.class%
TrayLoggerAbstract:
    Abstract: true
    Class: %logger.class%
    Calls:
      - [addHandler, [@UdpHandler]]
WebinySystem:
    Parent: @TrayLoggerAbstract
    Arguments: [System, %logger.driver.class%]
WebinyEcommerce:
    Parent: @TrayLoggerAbstract
    Arguments: [Ecommerce, %logger.driver.class%]
```
Now we need to register parameters and services with `ServiceManager`:

```php
// Registering multiple parameters at once
$parameters = Config::getInstance()->yaml($pathToYourParametersConfigFile);
ServiceManager::getInstance()->registerParameters($parameters);

// Registering one parameter
ServiceManager::getInstance()->registerParameter('someClassName', '\Webiny\Some\Class\Name');

// Registering your services
$servicesConfig = Config::getInstance()->yaml($pathToYourServicesConfigFile);
ServiceManager::getInstance()->registerServices('Logger', new ConfigObject($servicesConfig));
```

In this example we defined and abstract service `TrayLoggerAbstract` and 2 real loggers that extend the abstract service, `WebinySystem` and `WebinyEcommerce`. These 2 loggers share same class and method calls, but have different constructor arguments.

You can also specify arguments in abstract class and later override them in the real class. Also, you can add more method calls from child service:

```yaml
# Services
UdpHandler:
    Class: %logger.handler.udp.class%
TrayLoggerAbstract:
    Abstract: true
    Class: %logger.class%
    Arguments: [Default, %logger.driver.class%]
    Calls:
      - [addHandler, [@UdpHandler]]
WebinySystem:
    Parent: @TrayLoggerAbstract
    Calls:
    - [setSomething, [someParameter]]
WebinyEcommerce:
    Parent: @TrayLoggerAbstract
    Arguments: [Ecommerce, %logger.driver.class%]
```

In this last example, `WebinySystem` service will be constructed using the arguments from parent service and will also add an extra method call. `WebinyEcommerce` will provide it's own arguments to the parent constructor and will inherit the parent's `Calls`.

If you need to replace a method in `Calls` parameter, specify the third argument in call definition with the index of method to replace. In the following example, child method `setSomething` will replace the parent method at index 0, which is `addHandler`:

```yaml
UdpHandler:
    Class: %logger.handler.udp.class%
TrayLoggerAbstract:
    Abstract: true
    Class: %logger.class%
    Arguments: [Default, %logger.driver.class%]
    Calls:
      - [addHandler, [@UdpHandler]]
WebinySystem:
    Parent: @TrayLoggerAbstract
    Calls:
    - [setSomething, [someParameter], 0]
```

If you want to replace all of the parent `Calls`, put an exclamation mark in front of the `Calls` key, and make it look like this - `!Calls`:

```yaml
UdpHandler:
    Class: %logger.handler.udp.class%
TrayLoggerAbstract:
    Abstract: true
    Class: %logger.class%
    Arguments: [Default, %logger.driver.class%]
    Calls:
      - [addHandler, [@UdpHandler]]
WebinySystem:
    Parent: @TrayLoggerAbstract
    !Calls:
    - [setSomething, [someParameter]]
```

In this case, child service `Calls` will completely replace parent `Calls`.

## Accessing services from PHP

To use `ServiceManager` in your code, the easiest way is to simply use `ServiceManagerTrait`. This will give you access to `$this->service()`.

```php
class YourClass{
    use ServiceManagerTrait;
    
    public function yourMethod(){
        $service = $this->service('YourServiceName');
    }
}
```

If you do need to access ServiceManager class directly, use it like this:

```php
ServiceManager::getInstance()->getService('YourServiceName')
```

## Accessing services by tags
You can group services by using tags and load all of related services using single call. To achieve that, you need to add `tags` key to your service configuration:


```yaml
WebinySystem:
    Parent: @TrayLoggerAbstract
    !Calls:
    - [setSomething, [someParameter]]
    Tags: [logger]
WebinyCustom:
    Parent: @TrayLoggerAbstract
    Tags: [logger, custom_logger]
```

Now execute the following piece of code. The result will be an array containing two services: `WebinySystem` and `WebinyCustom`:

```php
class YourClass{
    use ServiceManagerTrait;
    
    public function yourMethod(){
        $services = $this->servicesByTag('logger');
    }
}
```

You can also tell `ServiceManager` to filter the services using a given interface or a class name. It fill first fetch all services containing the requested tag and then filter them using the given class or interface name, before returning the final result set to you. This way you are sure you only get what you need and don't have to make checks yourself, resulting in a cleaner code:

```php
class YourClass{
    use ServiceManagerTrait;
    
    public function yourMethod(){
        $services = $this->servicesByTag('cms_plugin', '\Your\Expected\Class\Or\Interface');
    }
}
```

Resources
---------

To run unit tests, you need to use the following command:

    $ cd path/to/Webiny/Component/ServiceManager/
    $ composer.phar install
    $ phpunit