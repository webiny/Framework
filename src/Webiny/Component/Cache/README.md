Cache Component
===============
`Cache` component give you ability to store different information into memory for a limited time.

Install the component
---------------------
The best way to install the component is using Composer.

```bash
composer require webiny/cache
```
For additional versions of the package, visit the [Packagist page](https://packagist.org/packages/webiny/cache).

## Supported drivers

The cache component supports following cache drivers:
* `APC` (http://php.net/manual/en/book.apc.php)
    - only available in PHP 5.4, from PHP 5.5 APC is not supported
* `Couchbase` (http://www.couchbase.com/develop/php/current)
* `Memcache` (http://php.net/manual/en/book.memcache.php)
* `Redis` (http://redis.io/clients)

If you are not sure which driver to use, we suggest `Memcache`.

Based on the selected driver, you'll have to pass different options to the constructor.

## Requirements

The default bridged library is `Memory` by `Jamm` (https://github.com/jamm/Memory).
It is required that you add this library to the `ClassLoader` :

```php
    \Webiny\Components\ClassLoader::getInstance()->registerMap(['Jamm\Memory' => 'path to memory lib']);
```

For example:

```php
    // APC
    $cache = \Webiny\Component\Cache\Cache::APC('cache-id');

    // Couchbase
    $cache = \Webiny\Component\Cache\Cache::Couchbase('CacheService', 'username', 'password', 'bucket', '127.0.0.1:8091');

    // Memcache
    $cache = \Webiny\Component\Cache\Cache::Memcache('CacheService', 'localhost', 11211);

    // Redis
    $cache = \Webiny\Component\Cache\Cache::Redis('CacheService', 'localhost', 6379);
```

## Common operations

Once you have created your `Cache` instance, you can start using your cache.
The cache methods are the same, no matter which driver you use:

```php
    // write to cache
    $cache->save('myKey', 'some value', 600, ['tag1', 'tag2']);

    // read from cache
    $cache->read('myKey');

    // delete from cache
    $cache->delete('myKey');

    // delete by tag
    $cache->deleteByTag(['tag1']);
```

## Cache config

The preferred way of defining cache drivers is creating them inside your the config file of your application.

```yaml
    Cache:
      TestCache:
        Factory: "\Webiny\Component\Cache\Cache"
        Method: "Apc"
      SomeOtherCache:
          Factory: "\Webiny\Component\Cache\Cache"
          Method: "Memcache"
          Arguments: ['127.0.0.1', '11211']
```

See the `ExampleConfig.yaml` for additional details.

Under `Cache` you define the cache drivers by giving each of them a `cache id` and underneath you nest its config.
The driver configuration depends on which driver you are using.

If you wish to turn off the cache, use the `BlackHole` driver.

The `Method` parameter must be a valid callback function that will return an instance of `CacheStorage`.

The benefit of defining cache drivers in this way is that the drivers are initialized the second Webiny Framework is loaded.
This enables you to access the cache by 'CacheTrait'.

```php
    class MyClass
    {
        use \Webiny\Component\Cache\CacheTrait;

        public function myMethod(){
            $this->cache('Frontend')->read('cache_key');
        }
    }
```

## Custom `Cache` driver

To implement you own custom cache driver you must first create a class that will implement `\Webiny\Component\Cache\Bridge\CacheInterface`.
Once you have that class, create another class with a static function that will return an instance of `CacheDriver`.

```php
    class CustomCacheDriver implements \Webiny\Component\Cache\Bridge\CacheInterface
    {
        // implement the interface methods

        // static method that will return the CacheDriver
        function getDriver($cacheId, $param1, $param2, array $options){
            $driver = new static($cacheId, $param1, $param2);

            return \Webiny\Component\Cache\CacheDriver($driver, $options);
        }
    }
```

Now just set your class and the static method as the `Method` inside your config and you can access the cache
over the `CacheTrait`.

Resources
---------

To run unit tests, you need to use the following command:

    $ cd path/to/Webiny/Component/Cache/
    $ composer.phar install
    $ phpunit

Make sure that you also set your cache driver details in `Tests/ExampleConfig.yaml`.