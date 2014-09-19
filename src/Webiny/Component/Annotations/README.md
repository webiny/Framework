Annotations Component
=====================
This simple component lets you read annotations assigned to a `class`, `method` or a `property`.

Install the component
---------------------
The best way to install the component is using Composer.

```json
{
    "require": {
        "webiny/annotations": "1.0.*"
    }
}
```
For additional versions of the package, visit the [Packagist page](https://packagist.org/packages/webiny/annotations).

Once you have your `composer.json` file in place, just run the install command.

    $ php composer.phar install

To learn more about Composer, and how to use it, please visit [this link](https://getcomposer.org/doc/01-basic-usage.md).

Alternatively, you can also do a `git checkout` of the repo.


## Configuration and setup
The component configuration is minimal, you just need to define the bridge dependency.
The built-in bridge uses [Minime\Annotations](https://github.com/marcioAlmada/annotations) library.

```yaml
Annotations:
    Bridge: \Webiny\Component\Annotations\Bridge\Minime\Annotations
    # You don't need this part if you are using Composer autoload.
    ClassLoader:
        Minime\Annotations: /var/www/Vendors/Minime/Annotations/src
```

Once defined, you can start parsing the annotations. The best approach is to use the `AnnotationsTrait`.
Let's say that this is the class that you wish to parse the annotations from.

```php
/**
 * This is a class description with some annotations.
 *
 * @prop SomeProperty that has a string value.
 * @author.name AuthorName
 * @author.email author@name.com
 * @author.website.url http://www.webiny.com
 * @author.website.desc My website
 *
 */
class TestClass
{
    /**
     * @var SomeVarAnnotation
     * @public
     */
    var $someVar;

    /**
     * @access.role ADMIN
     * @access.level 3
     * @name SomeName
     */
    private $_anotherVar;

    /**
     * @post @get
     * @cache.ttl 10
     * @cache.store true @cache.key cacheKey
     * @accept ["json", "xml"]
     */
    function someMethod()
    {

    }
}
```

In your class, where you wish to do the actually parsing, just use the `AnnotationsTrait`.
Trait methods return an instance of `ConfigObject` giving you the option to use the `get` method and to chain the namespaces.
Annotation namespaces are fully supported, giving you the possibility to organize the annotations in much cleaner way.

```php
class MyClass
{
    use AnnotationsTrait;

    function getClassAnnotations()
    {
        $classAnnotations = $this->annotationsFromClass('TestClass');
        $classAnnotations->prop; // SomeProperty that has a string value.
        $classAnnotations->author->website->url; // http://www.webiny.com
    }

    function getPropertyAnnotations()
    {
        $someVarPropertyAnnotations = $this->annotationsFromProperty('TestClass', 'someVar');
        $someVarPropertyAnnotations->public; // returns "true"
    }

    function getMethodAnnotations()
    {
        $methodAnnotations = $this->annotationsFromMethod('TestClass', 'someMethod');
        $methodAnnotations->cache->key; // cacheKey
        $methodAnnotations->accept; // [0 => "json", 1 => "xml"]
    }
}
```

Resources
---------

To run unit tests, you need to use the following command:

    $ cd path/to/Webiny/Component/Annotations/
    $ composer.phar install
    $ phpunit