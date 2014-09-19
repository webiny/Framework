StdLib
======
The standard library is a set of core Webiny framework components that make coding a bit more pleasurable when working with some low level objects like strings, array, date time and url.

Install the component
---------------------
The best way to install the component is using Composer.

```json
{
    "require": {
        "webiny/std-lib": "dev-master"
    }
}
```
For additional versions of the package, visit the [Packagist page](https://packagist.org/packages/webiny/std-lib).
Optionally you can add `"minimum-stability": "dev"` flag to your composer.json.

Once you have your `composer.json` file in place, just run the install command.

    $ php composer.phar install

To learn more about Composer, and how to use it, please visit [this link](https://getcomposer.org/doc/01-basic-usage.md).

Alternatively, you can also do a `git checkout` of the repo.

## About

The standard library consists of following libraries:
- [Exception](Exception)
- [StdObject] (StdObject)

.. and following helper traits:
- ComponentTrait
- FactoryLoaderTrait
- SingletonTrait
- ValidatorTrait
- StdLibTrait
- StdObjectTrait

## ComponentTrait
This trait is used when creating a new Webiny Framework component. By implementing this trait you automatically get the possibility to register the required class loader libraries, services and additional parameters for your new component. The trait is based on two main methods `setConfig` and `getConfig`, both methods are defined as `public static`.

Here is an example Webiny Framework component that uses the `ComponentTrait`.

```php
$crypy = \Webiny\Component\Crypt::setConfig('ExampleConfig.yaml');
```
The `ExampleConfig.yaml` file is defined like this:

```yaml
Crypt:
    Services:
        Password:
            Class: \Webiny\Component\Crypt\Crypt
            Arguments: [Blowfish, CCM, rijndael-128, _FOO_VECTOR]
        Cookie:
            Class: \Webiny\Component\Crypt\Crypt
            Arguments: [Blowfish, CCM, rijndael-128, _FOO_VECTOR]
    Bridge: \Webiny\Component\Crypt\Bridge\CryptLib\CryptLib
    ClassLoader:
        CryptLib: /var/www/Vendors/PHP-CryptLib/lib/CryptLib
```

There are couple of things you should notice:
- the root of yaml file matches the class name (`Crypt`) and the class name matches the root component namespace `\Webiny\Component\Crypt`
- the `Services` definition is based on rules defined by [ServiceManager)(../ServiceManager) component and all services defined in the yaml file are automatically registered with the `ServiceManager` under the name `ComponentName.ServiceName` in this example that would be `Crypt.Password` and `Crypt.Cookie`
- the `Bridge` is an optional parameter. You can define as many optional parameters as you want
- the `ClassLoader` parameter is an array that contains the namespace as key and path as a value. All the definitions under the `ClassLoader` parameter are automatically assigned to Webiny Framework ClassLoader component

So basically as you see, the `ComponentTrait` does a lot of handy stuff and makes Webiny Framework components very neatly organised.

### Callback
If you want to know when the configuration file on your class has been parsed, and all the services and class loader paths have been assigned, you can just define a `protected static function _postSetConfig` inside your component class, and the `ComponentTrait` will automatically call it each time you define a configuration for that class.

## FactoryLoaderTrait
A handy trait when you want to load some classes where the class name is defined as a string or inside a variable and you want to pass along some parameters to the constructor. You can also pass a interface or a class name that the factory class must implement/extend. This trait will first construct the object, with the given parameters, and then it will verify its instance against the given interface or a class. If everything matches, the class instance is returned, otherwise, and Exception is thrown.

```php
class MyClass
{
    use FactoryTrait;

    function myMethod()
    {
        // let's say we have a class we want to construct
        // the class has a constructor defined like this __construct($param1, $param2)
        // the class should implement \Webiny\Crypt\Bridge\CryptInterface
        $class = '\SomeVendor\SomeLib\SomeClass';

        try {
            $classInstance = $this->factory($class, '\Webiny\Crypt\Bridge\CryptInterface', ['foo1', 'foo2']);
        } catch (\Webiny\StdLib\Exception\Exception $e) {
            throw $e; // the class probably doesn't implement the required interface
        }
    }
}
```

## SingletonTrait
The `SingletonTrait` is used on classes that must implement [singleton pattern](http://en.wikipedia.org/wiki/Singleton_pattern). You just `use` the trait on the class you want to be singleton, and that's it.

There are two methods that you can optionally implement in your class, `public function init()` and `protected function _init()`, they are called only once and at the moment when you request a singleton instance.

```php
class MyClass
{
    use SingletonTrait;

    protected function __init()
    {
        // this will be called only once
        $this->__somePrivateMethod();
    }
}
```

To use your class you just call the static `getInstance` method that is implemented by the trait.

```php
$instance = MyClass::getInstance(); // this calls the internal __init method, but only the first time, when it creates the instance
```


## StdLibTrait
Also a helper trait with some commonly used functions. The trait itself only supports a limited number of methods, but we plan to expand it with more, so feel free to give suggestions.

This trait, not only that it defines helper methods, but it also `uses` `ValidatorTrait` and `StdObjectTrait` traits.

## StdObjectTrait
This trait provides helper functions when working with `StdObject` library.

```php
class MyClass
{
    use StdObjectTrait;

    public function test()
    {
        // create StringObject
        $this->str('This is a string');

        // create ArrayObject
        $this->arr(['one', 'two']);

        // create UrlObject
        $this->url('http://www.webiny.com/');

        // create DateTimeObject
        $this->dateTime('now');
    }
}
```

## ValidatorTrait
This is a helper trait with some common validation methods like `isNull`, `isEmpty`, `isInstanceOf` and many more.
Just view the class, all the methods are documented and, mostly self-explanatory.

Resources
---------

To run unit tests, you need to use the following command:

    $ cd path/to/Webiny/Component/StdLib/
    $ composer.phar install
    $ phpunit