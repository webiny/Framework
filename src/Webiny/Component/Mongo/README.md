Mongo Component
=================

Mongo Component is used for working with MongoDB database.

Install the component
---------------------
The best way to install the component is using Composer.

```json
{
    "require": {
        "webiny/mongo": "1.0.*"
    }
}
```
For additional versions of the package, visit the [Packagist page](https://packagist.org/packages/webiny/mongo).

Once you have your `composer.json` file in place, just run the install command.

    $ php composer.phar install

To learn more about Composer, and how to use it, please visit [this link](https://getcomposer.org/doc/01-basic-usage.md).

Alternatively, you can also do a `git checkout` of the repo.

## Configuring Mongo service

The recommended way of using Mongo is by defining a Mongo service. Here is an example of defining a service:

```yaml
Mongo:
    Services:
        Webiny:
            Class: \Webiny\Component\Mongo\Mongo
            Arguments: [127.0.0.1:27017, webiny, null, null, 'MyDatabase_']
    Driver: \Webiny\Component\Mongo\Driver\Mongo

```

Constructor arguments are in the following order: `host`, `database`, `username`, `password`, `collectionPrefix`.
Collection prefix will be automatically prepended for you to all database queries.

After you have defined your Mongo services (in most cases you will only need one, but you can have as many as you like), you can access your Mongo services by using `MongoTrait`:

```php
class MyClass {
    use MongoTrait;

    public function test(){
        // MongoTrait uses `Webiny` as a default service name
        $this->mongo()->getCollectionNames();

        // If you have specified your own service name, pass it to mongo method
        $this->mongo('MyMongo')->getCollectionNames();
    }
}
```

Resources
---------

To run unit tests, you need to use the following command:

    $ cd path/to/Webiny/Component/Mongo/
    $ composer.phar install
    $ phpunit

Make sure you set your MongoDb driver settings in `Tests\ExampleConfig.yaml`.