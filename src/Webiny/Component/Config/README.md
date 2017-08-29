Config Component
=====================
`Config` component creates `ConfigObject` instances from config files.
Currently supported formats: INI, JSON, PHP, YAML and custom drivers.

Install the component
---------------------
The best way to install the component is using Composer.

```bash
composer require webiny/config
```
For additional versions of the package, visit the [Packagist page](https://packagist.org/packages/webiny/config).

## Usage

To use `Config` component you will need a config file.

Example INI:

    [properties]
    a = "value"
    b.name = "name"
    b.value = "value"

Here is an example of creating a `ConfigObject`:
```php
    $config = \Webiny\Components\Config\Config::getInstance()->ini('path/to/file.ini');
```

This will result in $config object containing the following properties:

```php
    $config->properties->a = 'value';
    $config->properties->b->name = 'name';
    $config->properties->b->value = 'value';
```

If you don't want to use INI sections, or set custom nest delimiter, specify the following arguments:
```php
    $config = \Webiny\Components\Config\Config::getInstance()->ini('path/to/file.ini', false, '_');
```

You can get your config as string in any format using the following methods:
```php
    $string = $config->getAsJson();
    $string = $config->getAsPhp();
    $string = $config->getAsIni($useSections = true, $nestDelimiter = '.');
    $string = $config->getAsYaml($indent = 4);
```
And you can also use custom driver

```php
    $driverInstance = new MyCustomDriver();
    $string = $config->getAs($driverInstance);
```

You can also merge one config with another `ConfigObject` or array:
```php
$config->mergeWith($config2);
```

Resources
---------

To run unit tests, you need to use the following command:

    $ cd path/to/Webiny/Component/Config/
    $ composer.phar install
    $ phpunit
