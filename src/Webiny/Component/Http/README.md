Http Component
==============

The `Http` component consists of a `Request`, `Response`, `Cookie` and `Session` class.

Install the component
---------------------
The best way to install the component is using Composer.

```json
{
    "require": {
        "webiny/http": "1.0.*"
    }
}
```
For additional versions of the package, visit the [Packagist page](https://packagist.org/packages/webiny/http).

Once you have your `composer.json` file in place, just run the install command.

    $ php composer.phar install

To learn more about Composer, and how to use it, please visit [this link](https://getcomposer.org/doc/01-basic-usage.md).

Alternatively, you can also do a `git checkout` of the repo.

## Usage

The preferred way of accessing those classes is by using the `HttpTrait`.

```php
    class MyClass{
        use \Webiny\Component\Http\HttpTrait;

        function myFunction(){
            // access `Request` instance
            $this->httpRequest();

            // access `Cookie` instance
            $this->httpCookie();

            // access `Session` instance
            $this->httpSession();

            // create new `Response` instance
             $this->httpResponse('output content');
        }
    }
```

To register the config with the component, just call `Http::setConfig($pathToYamlConfig)`.

# `Request` class

The `Request` class provides different helper methods like:
- **getCurrentUrl** - returns current url
- **getClientIp** - returns the IP address of current client
- **isRequestSecured** - checks if the request is behind a 'https' protocol

And a lot of other methods.
**NOTE:** All of the functions check for forwarded response headers and validate them against
the defined list of trusted proxies.

Other than just providing helper functions, the `Request` class gives you also objective wrappers for working with
global variables like `$_SERVER`, `$_GET`, `$_POST` and `$_FILES`.

## `Server`

The `Server` class is a wrapper for all of (documented) $_SERVER properties, based on the list on official php.net documentation.
http://php.net/manual/en/reserved.variables.server.php

Here is an example usage:
```php
class MyClass{
        use \Webiny\Component\Http\HttpTrait;

        function myFunction(){
           // get request method
            $this->httpRequest()->server()->requestMethod(); // "GET"
        }
    }
```

**NOTE:** `Server` methods **do not** check forwarded response headers from reverse proxies. They are just an objective
wrapper for $_SERVER properties.
Use the methods from the `Request` class to get client ip, host name, and similar properties that validate against
trusted proxies.

## `$_GET` and `$_POST`

To access the `$_GET` properties use the `query` method, and to access the `$_POST` use the `post` method.
Both methods take two params. First param is the key of the property, and the second is the default value that will be
returned in case if the key does not exist.

Here is an example usage:
```php
class MyClass{
        use \Webiny\Component\Http\HttpTrait;

        function myFunction(){
            // get 'name' param from current query string
            $this->httpRequest()->query('name');

            // get 'color' param from $_POST, and if color is not defined, return 'blue'
            $this->httpRequest()->post('color', 'blue');
        }
    }
```

## `Payload`

To access the `Payload` property, use the `payload` method. `Payload` automatically reads `php://input` and `json_decode`s
the output.

To access payload values:
```php
class MyClass{
        use \Webiny\Component\Http\HttpTrait;

        function myFunction(){
            // get 'name' from payload
            $this->httpRequest()->payload('name');
        }
    }
```

## `$_FILES`

The `$_FILES` wrapper provides a much better way of handling uploaded files. The process consists of two steps. In the
first step, you get the file using the `files` method on the `Request` class. After that you can move the file to desired
destination.

```php
class MyClass{
        use \Webiny\Component\Http\HttpTrait;

        function myFunction(){
            // get the uploaded file
            $file = $this->httpRequest()->files('avatar');

            // move it to desired destination
            $file->store('/var/tmp');
        }
    }
```

# `Session` class

Webiny framework provides you with two built-in session storage handlers, the native handler and the cache handler.
Native handler uses the built-in PHP session handler, while cache handler uses the provided cache driver.
Using the cache handler you can easily share your sessions across multiple servers and boost performance. Current supported
cache drivers are all supported drivers by the `Cache` component.

## Session cache handler configuration

The default defined storage handler is the native handler. If you want to use the cache handler you must first setup a
cache driver (read the `Cache` component readme file) and then just link the cache driver to the session handler like this:

```yaml
    Http:
        Session:
            Storage:
                Driver: '\Webiny\Component\Http\Session\Storage\CacheStorage'
                Params:
                    Cache: 'TestCache'
                Prefix: 'wfs_'
                ExpireTime: 86400
```

There are two most important properties you have to change, the `Driver` and `Params.Cache`. The `Driver` property
must point to `\Webiny\Component\Http\Session\Storage\CacheStorage` and `Params.Cache` must have the name
of a registered `Cache` service.
No other changes are required in your code, you can work with sessions using the `Session` class.

## Custom session storage handler

You can implement your own session storage handler by creating a class that implements
`\Webiny\Component\Http\Session\SessionStorageInterface`. After you have created such a class, just point the
`Driver` param to your class and, optionally, pass the requested constructor params using the `Params` config attribute.

## Working with sessions

To work with sessions is rather easy, just access the current session handler which then provides you with the necessary
session methods like `get`, `save` and `getSessionId`.

Here is an example:

```php
class MyClass{
        use \Webiny\Component\Http\HttpTrait;

        function myFunction(){
            // save into session
            $this->httpSession()->save('my_key', 'some value');

            // read from session
            $this->httpSession()->get('my_key');
        }
    }
```

# `Cookie` class

Working with cookies is similar to working with sessions, you have a cookie storage handler that gives you the
necessary methods for storing and accessing cookie values. By default there is only a native built-in storage handler.

## Cookie configuration

The cookie configuration consists of defining the default storage driver and some optional parameters like `Prefix`,
`HttpOnly` and `ExpireTime`.

```yaml
    Http:
        Cookie:
            Storage:
                Driver: '\Webiny\Component\Http\Cookie\Storage\NativeStorage'
            Prefix: 'wfc_'
            HttpOnly: 'true'
            ExpireTime: 86400
```

## Custom cookie storage handler

To implement a custom cookie storage handler, you first need to create a storage handler class which implements the
`\Webiny\Component\Http\Cookie\CookieStorageHandler` interface. After you have successfully created your class,
you now have to change the `Storage.Driver` parameter in your cookie configuration to point to your class.

## Working with cookies

In order to read and store cookies you have to get the instance of current cookie storage driver which provides you with
the necessary methods. The `Cookie` class provides you with that access:

```php
class MyClass{
        use \Webiny\Component\Http\HttpTrait;

        function myFunction(){
            // save cookie
            $this->httpCookie()->save('my_cookie', 'some value');

            // read cookie
            $this->httpCookie()->get('my_key');
        }
    }
```

# `Response` class

The `Response` class provides methods for building an sending an output back to the browser.
The class itself doesn't require any configuration.

To create a `Response` instance, you can use the `HttpTrait`, the class constructor or `Response::create` static method.

```php

    // using the trait
    class MyClass{
        use \Webiny\Component\Http\HttpTrait;

        function myFunction(){
            // create and sent the output
            $this->httpResponse('Hello World!')->send();
        }
    }

    // using constructor
    $response = new Response('Hello World!');
    $response->send();

    // using static method
    $response = Response::create('Hello World!');
    $response->send();
```

## Methods

The `Response` class provides you with several helpful methods:
- `setContent`: sets the output content
- `setStatusCode`: sets the HTTP status code
- `setContentType`: sets the content type header (default is: "text/html")
- `setContentType`: sets the content char set (default is: "UTF-8")
- `setHeader`: sets or adds a header to the response

## Cache control

A cache control class is provided to control the cache control headers on the response object.
To access the cache control options, use the `cacheControl` method on the `Response` object.

```php
    $response = new Response('Hello World!');
    $cacheControl = $response->cacheControl();
```

`CacheControl` by default calls the `setAsDontCache` method which sets the cache control headers so that the response
is not cached by the browser. To overwrite that, you can either provide an array with your own cache control header information or you can just call the `setAsCache` method which sets the response cache headers so the output can be cached by the browser.

## `JsonResponse`

This class extends the `Response` class by setting the default content type to "application/json". The class can be accessed by a constructor or by a static short-hand method:

```php
    // using constructor
    $jsonResponse = new Response\JsonResponse($someArrayOrObject);
    $jsonResponse->send(); // send output to browser

    // short-hand
    Response\JsonResponse::sendJson($someArrayOrObject)); // output is automatically sent
```

Resources
---------

To run unit tests, you need to use the following command:

    $ cd path/to/Webiny/Component/Http/
    $ composer.phar install
    $ phpunit