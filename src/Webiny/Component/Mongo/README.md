Mongo Component
=================

Mongo Component is used for working with MongoDB database.

Install the component
---------------------
The best way to install the component is using Composer.

```bash
composer require webiny/mongo
```
For additional versions of the package, visit the [Packagist page](https://packagist.org/packages/webiny/mongo).

## Configuring Mongo service

The recommended way of using Mongo is by defining a Mongo service. Here is an example of defining a service:

```yaml
Mongo:
    Services:
        Webiny:
            Class: \Webiny\Component\Mongo\Mongo
            Arguments:
                Uri: 127.0.0.1:27017
                UriOptions: []
                DriverOptions: []
                CollectionPrefix: 'MyDatabase_'
            Calls: 
                - [selectDatabase, [MyDatabase]]
    Driver: \Webiny\Component\Mongo\Bridge\MongoDb
```

Collection prefix will be automatically prepended for you to all database queries.

For more information see: [mongodb/mongo-php-library](http://mongodb.github.io/mongo-php-library/)

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

### ResultClass
`ResultClass` is used to wrap all Mongo command results. This allows us to have a compatibility layer in case something changes in Mongo response structures in the future
and also allows any developer to extend this class and add custom methods to handle mongo response flags.

### Indexes
Currently Mongo component supports 3 types of indexes:
- SingleIndex
- CompoundIndex
- TextIndex

To create an index on your collection:
```php
// Create a unique single index object
$index = new SingleIndex('Name', 'name', false, true);

// Use mongo trait to create index on your collection
$this->mongo()->createIndex('MyCollection', $index);
```

Resources
---------

To run unit tests, you need to use the following command:

    $ cd path/to/Webiny/Component/Mongo/
    $ composer.phar install
    $ phpunit

Make sure you set your MongoDb driver settings in `Tests\ExampleConfig.yaml`.