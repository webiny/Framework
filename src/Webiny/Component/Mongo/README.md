Mongo Component
=================

Mongo Component is used for working with MongoDB database.

Install the component
---------------------
The best way to install the component is using Composer.

```json
{
    "require": {
        "webiny/mongo": "1.1.*"
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
            Arguments:
                Host: 127.0.0.1:27017
                Database: webiny
                Username: null
                Password: null
                CollectionPrefix: 'MyDatabase_'
                Options:
                    w: 1
                    wTimeoutMS: 10000
                    connectTimeoutMS: 60000
                    socketTimeoutMS: 30000
                    fsync: false
                    journal: false
    Driver: \Webiny\Component\Mongo\Driver\Mongo
    ResultClass: \Webiny\Component\Mongo\MongoResult

```

Collection prefix will be automatically prepended for you to all database queries.
`Options` do not need to be specified if you want to use default Mongo settings.

For more information see: [MongoClient options](http://php.net/manual/en/mongoclient.construct.php)

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