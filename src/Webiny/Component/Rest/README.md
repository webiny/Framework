REST Component
================

A simple but powerful REST library that doesn't get in the way.

Install the component
---------------------
The best way to install the component is using Composer.

```json
{
    "require": {
        "webiny/rest": "dev-master"
    }
}
```
For additional versions of the package, visit the [Packagist page](https://packagist.org/packages/webiny/rest).
Optionally you can add `"minimum-stability": "dev"` flag to your composer.json.

Once you have your `composer.json` file in place, just run the install command.

    $ php composer.phar install

To learn more about Composer, and how to use it, please visit [this link](https://getcomposer.org/doc/01-basic-usage.md).

Alternatively, you can also do a `git checkout` of the repo.

## Usage

Some of the built-in features:
- supports **GET**, **POST**, **PUT**, **PATCH** and **DELETE** requests
- integrated version management system
- effective Rate Control mechanism
- services are configured using annotations
- built in Cache using [Webiny Framework Cache component](../Cache/)
- built in ACL using [Webiny Framework Security component](../Security/)
- nice debug options
- pretty formatted JSON output (only in development mode)
- CRUD support

## Usage example

```php
// create REST instance for the given configuration and the API class
$rest = new \Webiny\Component\Rest\Rest('InternalApi', '\MyApp\Services\TestService');

// process the request and send the output to browser
$rest->processRequest()->sendOutput();

// simple as that...
```

## Configuration and dependencies

This is an example configuration:
```yaml
Rest:
    ExampleApi:
		CompilePath: /var/tmp
	SomeOtherApi:
	    CompilePath: /var/www/Cache/Rest
        Cache: someCacheService
        Security:
            Role: ROLE_ANONYMOUS
            Firewall: Admin
        RateControl:
         Limit: 60
         Interval: 1 # in minutes
         Penalty: 10 # in minutes
        Environment: production
```

As you can see, you can have multiple REST configurations. The minimum that one configuration must have is just the
definition of `CompilePath`.

### Configuration parameters:

#### **CompilePath**
This is the absolute path to a folder where the REST component will store the compiled files.

*I you want to know more:*
When you register a class, or in API naming, a "service", the component will parse through that class and all its
methods and their annotations, which would be then evaluated based on different rules, to define the service behaviour.
All this is then saved an array that is actually stored in a file, that we call the compile cache file.

#### **Cache**
As stated before, the component uses the [Cache component](../Cache/) from Webiny Framework. The value of the `Cache`
should point to a defined cache service.

The cache is used for two different operations, one is to provide a caching layer for storing results,
and the other one is that Cache is a requirement if you wish to use the Rate Control mechanism.

#### **Security**
Security section provides a layer for authorization and authentication above your REST APIs. It is dependent upon the
[Security](../Security) component.

The configuration takes two parameters:
- `Firewall`: name of the registered firewall on the Security component configuration.
- `Role`: this is the default role that all users need to have in order to access the API. You can overwrite the required role in the annotations. If you don't want to force the role, you can either remove this part from your configuration, or set it to `ROLE_ANONYMOUS`, which will then allow access to all users, unless it's overwritten by a method or class annotation.

If the user doesn't have access, a **403 - Forbidden** response is returned.

#### **RateControl**
Rate control is a protection mechanism preventing anyone from abusing your REST API in a way the he is making too many requests in a short period of time.

With rate control you set the following parameters:
- `Limit`: how many requests per interval a particular IP can make
- `Interval`: after how many minutes should be reset the limit
- `Penalty`: for how long should we block the IP if it has reached the limit

If limit is reached, and penalty is activated, the component will return **429 - Too Many Requests** response, until the
limit is restored.

Note that the rate control mechanism requires that you have `Cache` specified on that REST configuration.

#### **Environment**
The value of `Environment` attribute can either be 'production' or 'development'. The difference is that in development
mode we constantly rebuild the compiled cache files, we also output special debug response headers, and JSON output uses pretty format.

## Class and method annotations

```php
/**
 * @rest.role ROLE_EDITOR
 */
class FooService
{
    /**
     * @rest.method get
     * @rest.default
     * @rest.ignore
     * @rest.cache.ttl 100
     * @rest.header.cache.expires 3600
     * @rest.header.status.success 200
     * @rest.header.status.error 401
     * @rest.header.status.errorMessage No Author for specified id.
     * @rest.rateControl.ignore
     *
     * @param integer $param1 Some param.
     * @param string $param2 Other param.
     * @param string $param3 Other param.
     */
    function fooMethod($param1, $param2 = "default", $param3 = "p3def")
    {
        ...
    }
}
```

Annotations are a way of describing certain properties of an object. With annotations you can configure the behaviour
of your service. All the REST component annotations have a `rest` namespace.

All the annotations can be defined on a class level, making them default to methods, and on the method level you can
overwrite them. There are no required annotations.

The following annotations are available:

#### **@rest.method**

```php
/**
 * @rest.method get
 */
```

Defines over which HTTP method the service can be accessed. If not defined, `get` is set as default. The supported
request types are **GET**, **POST**, **PUT**, **PATCH** and **DELETE**.
They are not case sensitive.

#### **@rest.default**

```php
/**
 * @rest.default
 */
```

Defines that this method is the default method for the defined `@rest.method` request type.
For example if you have `@rest.method` set to `post` and the method is marked with `@rest.default` and you do a POST
request just to the service name, without the method, the defined default method for POST request will be triggered.

#### **@rest.ignore**

```php
/**
 * @rest.ignore
 */
```

This flag tells to the component that it should ignore that method and that it's not part of the service.
Usually used for some internal methods.

#### **@rest.cache.ttl**

```php
/**
 * @rest.cache.ttl 100
 */
```

Marks that the returned result from this method can be cached for the specific amount of time. The time is defined in
seconds. Note that this feature requires that you have a `Cache` service defined in your configuration.

#### **@rest.header**

There are several options in the `header` section that you can control:
- `cache.expires`: defines what ttl will be set in `Expires` header that component will send to the browser. If you don't set it, it will be set to '-1' telling the browser to always grab fresh content from the server.
- `status.success`: what response status code should be returned if the request was successful. By default **200 - OK** is returned, with an exception of **201 - Created** for **POST** requests.
- `status.error`: same as `status.success`, but in a case of an error. By default the system returns a **404 - Not Found**.
- `status.errorMessage`: defines a custom error message that will be attached to the response status code.


#### **@rest.role**

```php
/**
 * @rest.role ROLE_EDITOR
 */
```
Defines that a method can only be accessed by users that have the specific, or higher, access level.

This annotation requires that you define the `Security` section in your configuration.

#### **@rest.rateControl.ignore**

```php
/**
 * @rest.rateControl.ignore
 */
```

This flag marks that rate control will not be applied to that method.

## Routing and accessing the APIs

It's important to note that component doesn't provide any routing mechanisms, that is up to you. For example you can use [Webiny Framework Router](../Router).
The only requirement is that method name must be present, and if the method takes parameters, they need to be defined after the method name.

For example, if you have a class like this:
```php
class FooService
{

    /**
     * @param integer $param1 Some param.
     * @param string $param2 Other param.
     */
    function fooMethod($param1, $param2 = "default")
    {
        ...
    }
}
```

To access it, you would have a url looking something like this: `http://api.example.com/foo-service/foo-method/1/test/`.
The component does the detection from **foo-service/foo-method/** onwards, everything before that is ignored. (query parameters are also ignored)
Note that if you have a class name with a namespace, for example `MyApp\Cms\Page`, it's only required that you use the class name in the url `Page`,
namespace is optional.

As you can see, the class name and the method name were translated to `foo-service/foo-method`.
**The rule is that class name and method name must be lowercase with a dash '-', as the word separator.**

Also, the component can detect default parameters, so you don't need to define them in the url, meaning that, for the
upper example, you can also access it with this url `http://api.example.com/foo-service/foo-method/1/`.
The value of `$param2` would be `default`.

Be careful, if you define in the annotations, that a parameter is an integer, like in the upper example, in case of
`$param1`, you will not be able to access that method with a url looking like this: `http://api.example.com/my-service/foo-method/asd/`
because the component detects strings, integers and booleans.

## Interfaces

The component provides several interfaces that you can implement on your API class to gain more control over some aspects
of the component.

All the interfaces are under the a namespace `Webiny\Component\Rest\Interfaces\`.

### Versioning and VersionInterface

The component gives you the option to version your APIs, meaning that you can have multiple active version of one API.
This helps a lot when you are deploying a new version, but you still need to support the old one.

Also you have two version aliases, making things even more simpler for you. The alias is nothing but a pointer to an actual version.
The two available aliases are `latest` and `current`. If somebody requests your API, and if he hasn't defined a version, he will be pointed
to the `current` version, which is then mapped to an actual version.

In order to implement versioning feature, you need to implement `Webiny\Component\Rest\Interfaces\VersionInterface` on your class.
This looks something like this:

```php
class FooService implements \Webiny\Component\Rest\Interfaces\VersionInterface
{

    static public function getLatestVersion(){
        return '2.0';
    }

    static public function getCurrentVersion(){
        return '1.0';
    }

    static public function getAllVersions(){
        return [
            '1.0' => 'FooService',
            '2.0' => 'FooServiceNew',
            '2.1' => 'FooServiceBetaInTesting'
        ];
    }
}
```

The interface will tell you to implement the upper three methods, `getLatestVersion`, `getCurrentVersion` and `getAllVersions`.
The most important method is the `getAllVersions` which returns an array of supported versions where the key is the version number,
in format X.Y, and the class name is the value. This is the class that will be used to handle the requests.

Note that only the 'main' class needs to be registered with the component `$rest = new \Webiny\Component\Rest\Rest('InternalApi', 'FooService');`.
Also the main class is the only one that needs to implement the interface, making everything a whole lot easier to maintain.

#### How to access a specific version

By default all users will be pointed to the `current` version. To make a request to a specific version you need to add a request header.
The request header name is `X-Webiny-Rest-Version` and the value is the version. For the version you can send a specific version
number, or an alias. All requests that have this header will be mapped to that concrete version.

// point the request to version 2.1
```txt
X-Webiny-Rest-Version: 2.1
```

### SecurityInterface

If you wish to implement your own security layer, you can implement the `Webiny\Component\Rest\Interfaces\SecurityInterface`.

```php
class FooService implements \Webiny\Component\Rest\Interfaces\VersionInterface
{

    public function hasAccess($role)
    {
        // do you processing here
    }
}
```

The interface will ask you to define `hasAccess` method. This method takes only one parameter `$role`. This parameter
contains the value defined in `@rest.role` annotation. The method should return either `true` or `false`, allowing or denying access to the user.


### CacheKeyInterface

Rest component, by default, creates cache keys from these parameters:
 - url path
 - query parameters
 - http method
 - post parameters
 - payload parameters
 - api version (we use the actual version number, not the aliases like current, and latest)

Implement this interface to define your own method for generating a cache key.
Some common use cases are to generate a cache key based on some cookie or token.
Note that you should still include the url, query parameters and the http method.
Always take into account that generating the cache key doesn't actually take longer than getting the data without cache.

The implementation looks like this:


```php
class FooService implements \Webiny\Component\Rest\Interfaces\CacheKeyInterface
{

    public function getCacheKey($role)
    {
        // return your generated key
    }
}
```

Note that the returned key is used "as it is", nothing is appended to it, nor it is hashed, so make sure that you
return a key with a proper size.

### CrudInterface

Implementing this interface you will get the basic CRUD methods and behavior described in the table below:

|  Request type   |        Url         |       Mapping               |  Description                           |
| --------------- | ------------------ | --------------------------- | -------------------------------------- |
|      GET        |    foo-class/      |  FooClass::crudList         | Retrieve all records in a collection.  |
|      POST       |    foo-class/      |  FooClass::crudCreate()     | Create new record.                     |
|      DELETE     |    foo-class/{id}  |  FooClass::crudDelete($id)  | Delete a record with the given id.     |
|      GET        |    foo-class/{id}  |  FooClass::crudGet($id)     | Retrieve a single record.              |
|      PUT        |    foo-class/{id}  |  FooClass::crudReplace($id) | Replace a single record.               |
|      PATCH      |    foo-class/{id}  |  FooClass::crudUpdate($id)  | Update a single record.                |


## RestTrait

In practice you often need to use things like paging, sorting and similar,
which doesn't make since to put as a parameter in your method. The best approach is to use query parameters.
The `RestTrait` provides you with helper functions and suggestions.

In the trait you will find the next methods:
- `restGetPage`: returns the value of `_page` query parameter
- `restGetPerPage`: returns the value of `_perPage` query parameter (has a built-in limit of 1.000)
- `restGetSortField`: returns the sort field name from the `_sort` query parameter
- `restGetSortDirection`: returns the sort direction from the `_sort` query parameter
- `restGetFields`: returns the value of `_fields` query parameter

Let's see the returned values if we would look at this url:
`http://api.example.com/my-service/get-pages/?_page=1&_perPage=10&_sort=+Title&_fields=id,title,author,slug`

The returned values would be as following:
- `restGetPage`: 1
- `restGetPerPage`: 10
- `restGetSortField`: Title
- `restGetSortDirection`: 1 (if we would have '-' in front of the field name, the function would return -1)
- `restGetFields`: id,title,author,slug

## Return values

The component returns a JSON response, like the one below:

```json
{
    "data": "this is my result"
}
```

Your result is always encapsulated within the `data` property.

In case of an error, the `data` property is omitted, and you will get a response containing errors, like this one:

```json
{
    "errorReport": {
        "message": "This is an error.",
        "description": "Some custom error description."
    }
}
```

You can also add additional error entries:

```json
{
    "errorReport": {
        "message": "This is an error.",
        "description": "Some custom error description.",
        "errors": [
            {
                "message": "This is an additional error message.",
                "field": "This is a custom error field."
            },
            {
                "message": "Another error",
                "code": "23a33"
            }
        ]
    }
}
```

### Throwing errors

When you need to throw an error, the best way is using the RestErrorException class.

```php
class FooService
{
    public function testError()
    {
        $error = new \Webiny\Component\Rest\RestErrorException("This is an error.", "Some custom error description.");
        $error->addError(['message'=>'This is an additional error message.', 'field'=>'This is a custom error field.']);
        $error->addError(['message'=>'Another error', 'code'=>'23a33']);

        throw $error;
    }
}
```

## Debugging

The component will return additional debug information in the response header and in the `debug` part of the response body.
The additional headers are as following:

- `X-Webiny-Rest-Class`: name of the used API class (useful to know which class was used based on the version)
- `X-Webiny-Rest-ClassVersion`: actual API version
- `X-Webiny-Rest-CompileCacheFile`: absolute path to the compiled cache file (it's useful to refer to that file for additional information)
- `X-Webiny-Rest-Method`: which HTTP request method was used
- `X-Webiny-Rest-RateControl-Limit`: the limit of rate control (also present in the production mode)
- `X-Webiny-Rest-RateControl-Remaining`: the remaining number of requests, until the limit is reached (also present in the production mode)
- `X-Webiny-Rest-RateControl-Reset`: unix timestamp with the date when the rate control limit will be refreshed
- `X-Webiny-Rest-RequestedRole`: present only if the method required some specific role

Here is typical example output:
```txt
X-Webiny-Rest-Class:TestRestApiServiceNew
X-Webiny-Rest-ClassVersion:2.0
X-Webiny-Rest-CompileCacheFile:/var/www/projects/webiny/Public/Cache/Rest/InternalApi/TestRestApiService/current.php
X-Webiny-Rest-Method:GET
X-Webiny-Rest-RateControl-Limit:10
X-Webiny-Rest-RateControl-Remaining:8
X-Webiny-Rest-RateControl-Reset:1408414512
X-Webiny-Rest-RequestedRole:SECRET_ROLE
```

Resources
---------

To run unit tests, you need to use the following command:

    $ cd path/to/Webiny/Component/Rest/
    $ composer.phar install
    $ phpunit

Make sure that you update the configuration files inside `Test/Mocks/` folder.