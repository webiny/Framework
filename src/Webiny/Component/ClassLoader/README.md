ClassLoader Component
=====================
Class loader component loads your PHP files automatically as long as they follow some standard naming convention.
The following standards are supported:
- PEAR
- [PSR-0](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md)
- [PSR-4](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md)

Install the component
---------------------
The best way to install the component is using Composer.

```json
{
    "require": {
        "webiny/class-loader": "1.1.*"
    }
}
```
For additional versions of the package, visit the [Packagist page](https://packagist.org/packages/webiny/class-loader).

Once you have your `composer.json` file in place, just run the install command.

    $ php composer.phar install

To learn more about Composer, and how to use it, please visit [this link](https://getcomposer.org/doc/01-basic-usage.md).

Alternatively, you can also do a `git checkout` of the repo.


## Usage

To use the ClassLoader, get its instance by calling ClassLoader::getInstance() method.

```php
    require_once 'Webiny/Component/ClassLoader/ClassLoader.php'

    use Webiny\Component\ClassLoader;

    ClassLoader::getInstance();
```

Once you have the ClassLoader instance, you can register map rules. The ClassLoader automatically detects if you are
registering a namespace or a PEAR rule. PEAR rules are identified by having a underline '_' at the end of the prefix.
If PSR is not defined, the component will use **PSR-4** standard. All paths should be absolute.

```php
    ClassLoader::getInstance()->registerMap([
    										// a namespace rule (PSR-4 - default)
    										'Webiny' => realpath(dirname(__FILE__)).'/library/Webiny',
    										// a namespace rule (PSR-0)
    										'Symfony' => [
    										    'Path' => '/var/vendors/Symfony',
    										    'Psr' => 0
    										],
    										// a PEAR rule
    										'Swift_' => realpath(dirname(__FILE__)).'/library/Swift',
    										]);
```

As you can see the registerMap method takes an array of multiple rules. Each rule consists of a prefix and a location.

For better performance you can provide a Cache component to ClassLoader. Doing so, ClassLoader will cache the paths and
files resulting in a faster performance.

```php
    ClassLoader::getLoader()->registerCacheDriver($instanceOfCacheInterface);
```

## Non-standardized libraries

If you have a library that is not following neither the PSR naming convention nor the PEAR naming convention, you'll
have to manually define some of the settings.

Let's take a look at this example:
```php
    ClassLoader::getInstance()->registerMap([
        'Smarty_' => [
                        'Path'      => '/var/www/Vendors/Smarty/libs/sysplugins',
                        'Normalize' => false,
                        'Case'      => lower
                     ]
    ]);
```

You can see that the `Smarty_` library is defined as an array that has `Path`, `Normalize` and `Case` parameter.

### `Path`

Defines the path to the library.

### `Normalize`

The `Normalize` parameter tells the autoloader if he should to change the `_`, on the class name, into directory separators.
For example if you have a class names`Smarty_Internal_Compile` the normalized path would be `Smarty/Internal/Compiler`.
If you set the `Normalize` parameter to `false`, the original class name will be used.

### `Case`

By default the autoloader transfers all the class names to CamelCase, you can set the `Case` parameter to `lower` if
you wish that the class names are used in lower case inside the class path.

Resources
---------
To run unit tests, you need to use the following command:

    $ cd path/to/Webiny/Component/ClassLoader/
    $ composer.phar install
    $ phpunit