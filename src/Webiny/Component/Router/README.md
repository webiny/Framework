Router Component
================

Router component is used for mapping defined paths/urls to appropriate controllers or services.

Install the component
---------------------
The best way to install the component is using Composer.

```json
{
    "require": {
        "webiny/router": "1.0.*"
    }
}
```
For additional versions of the package, visit the [Packagist page](https://packagist.org/packages/webiny/router).

Once you have your `composer.json` file in place, just run the install command.

    $ php composer.phar install

To learn more about Composer, and how to use it, please visit [this link](https://getcomposer.org/doc/01-basic-usage.md).

Alternatively, you can also do a `git checkout` of the repo.

## Usage

Defining a route is a rather easy process, you set a route name and underneath you define the path and the callback.
Here is an example:

```yaml
    Router:
        Routes:
            BlogTag:
                Path: blog/tag/{tag}
                Callback: MyApp\Blog\showTag
            BlogComments:
                Path: blog/post/{id}/comments
                Callback:
                    Class: MyApp\Blog
                    Method: showComments
            BlogPost:
                Path: blog/post/{slug}/{id}
                Callback: MyApp\Blog\showPost
                Options:
                    slug:
                        Pattern: .*?
            BlogAuthor:
                Path: blog/authors/{author}
                Callback: MyApp\Blog\showAuthorPosts
                Options:
                    author:
                        Default: webiny
            Blog:
                Path: blog
                Callback: MyApp\Blog\index
```

If a route is matched you will get an instance of `MatchedRoute`. The `getCallback` method returns the value of callback
parameter of the matched route. The second method `getParams` returns the values of the parameters defined in the `Path` section.

By registering a default `Router` configuration, `Router` will automatically set the defined routes and cache driver.
```php
    Router::setConfig($pathToYourConfig);
```

Later on you can use the `RouterTrait` access that `Router` instance.

**NOTICE:**
When defining the routes in your config file, make sure that you put routes with lesser precision to the bottom, and
routes with a more stricter rules at the top. A common pitfall is that a route with more wider pattern is matched before
a route with a more stricter rules due to the fact that the `Router` loops over the defined routes in the same order
that they are defined inside the config file, and it stops once the first route is matched.

### `Options`

The `Options` attribute under the route provides you two additional options that you can set under each route.

#### `Pattern`

The default regex pattern for matching a variable is `{[\w-]+}`. Using the `Pattern` attribute you can set your own rule.

#### `Default`

The `Default` attribute gives you the option to set the the default value for a variable.

If the route is not matched using the current available properties from the url path, `Router` will replace all the
 variable patterns with the default values and try to match the route again.

## Matching routes

To check if a route matches the given path, use the `RouterTrait` and then just call `$this->router()->match()` method.
The `match` method takes either a string that is representing a url or it can take an instance of `Url` standard object.
If the router successfully matches one of the routes it will return an array with callback and params attributes.
If the router doesn't match any of the routes, it will return false.

```php
class MyClass
{
	use \Webiny\Component\Router\RouterTrait;

	function __construct(){
		$result = $this->router()->match('blog/tag/some_tag');
		$result = $this->router()->match('http://www.webiny.com/blog/post/some-post/12');
	}
}
```

**NOTE:** `Router` component always returns the **first route** that matches the given path.

## Executing route callback

If you define your callback as string, you will have to parse and execute it on your own. But if you follow the standard
structure of your callback you will be able to use router's `execute` method and pass your `MatchedRoute` to it.
Router will then execute the callback for you and do all the checks regarding class and method existence:

```yaml
BlogComments:
    Path: blog/post/{id}/comments
    Callback:
        Class: MyApp\Blog
        Method: showComments
```

```php
class MyClass
{
	use \Webiny\Component\Router\RouterTrait;

	function __construct(){
		$result = $this->router()->match('blog/post/12/comments');
		if($result){
		    $callbackResult = $this->router()->execute($result);
		}
	}
}
```

If for some reason you need to call the method statically, define your route callback like this:

```yaml
BlogComments:
    Path: blog/post/{id}/comments
    Callback:
        Class: MyApp\Blog
        Method: showComments
        Static: true
```

## Generating routes

With the `Router` your don't have write the urls inside your code or views, instead you can generate the urls from the
given route like this:

```php
class MyClass
{
	use \Webiny\Component\Router\RouterTrait;

	function __construct(){
		$url = $this->router()->generate('BlogTag',  ['tag'=>'html5', 'page'=>1]);
		// http://www.webiny.com/blog/tag/html5/?page=1
		// page was not defined in the route, that's why it's appended as a query param

		$url = $this->router()->generate('BlogAuthor');
		// http://www.webiny.com/blog/authors/webiny
		// the value of "author" was not set, so the generator used the default value
	}
}
```

You see that `Router` replaced the `{tag}` parameter with the provided value, in this case it was `html5`. You can also
notice that the `page` parameter isn't defined in our route, so the `Router` appended that parameter as a query string.

## Configuration

The `Router` component takes one additional configuration parameter, and that is the `Cache` parameter. If either defines
the name of the cache service that will be used to cache some of the internal mechanisms, or you can set it to `false` if
you don't want the component to use cache.

```yaml
Router:
    Cache: TestCache
```

Using the cache can speed up matching of routes, especially more complex ones.

Resources
---------

To run unit tests, you need to use the following command:

    $ cd path/to/Webiny/Component/Router/
    $ composer.phar install
    $ phpunit