Template Engine Component
=========================

`TemplateEngine` component provides a layer for rendering view templates.

Install the component
---------------------
The best way to install the component is using Composer.

```json
{
 "require": {
     "webiny/template-engine": "1.0.*"
 }
}
```
For additional versions of the package, visit the [Packagist page](https://packagist.org/packages/webiny/template-engine).

Once you have your `composer.json` file in place, just run the install command.

 $ php composer.phar install

To learn more about Composer, and how to use it, please visit [this link](https://getcomposer.org/doc/01-basic-usage.md).

Alternatively, you can also do a `git checkout` of the repo.

### Installation error

If you get an error like this:

```
[RuntimeException]
Package could not be downloaded, sh: 1: svn: not found
```

Then probably you don't have Subversion installed which is preventing the installation of Smarty library.
This can be easily solved by installing the Subversion, prior to the composer install.

    $ sudo apt-get install subversion


## Usage

The definition of the view template depends
on the selected driver. By default the template engine comes with a driver for `Smarty`, but you can easily add
support for `Twig` or some other template engines.

The provided functionality of every driver is defined by the `TemplateEngineInterface` which defines the following methods:
- **fetch** - fetch the template from the given location, parse it and return the output
- **render** - fetch the template from the given location, parse it and output the result to the browser
- **assign** - assign a variable and its value into the template engine
- **setTemplateDir** - directory where the template files are stored
- **registerPlugin** - register a plugin for the template engine

To create a new driver just create a new class that implements the `\Webiny\Component\TemplateEngine\Bridge\TemplateEngineInterface`
and adapt your config.

The default configuration looks like this:
```yaml
    TemplateEngine:
        Engines:
            Smarty:
                ForceCompile: false
                CacheDir: '/var/tmp/smarty/cache'
                CompileDir: '/var/tmp/smarty/compile'
                TemplateDir: '/var/www/theme/templates'
                AutoEscapeOutput: false
```

## Usage

The preferred usage is over the `TemplateEngineTrait`.
Here is an example:

```php
class MyClass
{
	use \Webiny\Component\TemplateEngine\TemplateEngineTrait;

	function __construct() {
	    // assing name and id to the template and render it
		$this->templateEngine('Smarty')->render('template.tpl', ['name'=>'John', 'id'=>15]);
	}
}
```

### Smarty

If you wish to use the Smarty template engine, with the built in driver, make sure you include
`path/to/Smarty/libs/Smarty.class.php` somewhere inside you application, before using the component. This is due to the
problem that Smarty doesn't provide a suitable autoloader to be integrated with the component.

## Plugins & extensions

The template engine is designed so that it can be expanded with different plugins and modifiers, depending on the assigned driver.

Best practice for expanding the template engine is first to create an extension and then register it as a service
tagged with the `$driverName.Extension`, for example `Smarty.Extension`.

An `Extension` is a package of one or multiple plugins. Plugin type depends on the template engine, for example, Smarty
supports these plugin types:
- **functions** - http://www.smarty.net/docs/en/plugins.functions.tpl
- **modifiers** - http://www.smarty.net/docs/en/plugins.modifiers.tpl
- **blocks** - http://www.smarty.net/docs/en/plugins.block.functions.tpl
- **compiler functions** - http://www.smarty.net/docs/en/plugins.compiler.functions.tpl
- **pre filters** - http://www.smarty.net/docs/en/plugins.prefilters.postfilters.tpl
- **post filters** - http://www.smarty.net/docs/en/plugins.prefilters.postfilters.tpl
- **output filters** - http://www.smarty.net/docs/en/plugins.outputfilters.tpl
- **resources** - http://www.smarty.net/docs/en/plugins.resources.tpl
- **inserts** - http://www.smarty.net/docs/en/plugins.inserts.tpl

To create a smarty extension, create a class that extends `\Webiny\Components\TemplateEngine\Drivers\Smarty\SmartyExtension`
and then overwrite the methods, based on the plugin type your wish to create.

For example, let's say we want to register a modifier called 'customUpper'. First we create our extension class like this:

```php
namespace MyApp\Demo;

class MySmartyExtension extends \Webiny\Component\TemplateEngine\Drivers\Smarty\SmartyExtension
{
	/**
	 * @overwrite
	 * @return array
	 */
	function getModifiers(){
		return [
			new SmartySimplePlugin('custom_upper', 'modifier', [$this, 'customUpper'])
		];
	}

	/**
	 * Callback for my custom_upper modifier.
	 *
	 * @param $params
	 *
	 * @return string
	 */
	function customUpper($params){
		return strtoupper($params);
	}

	/**
	 * Returns the name of the plugin.
	 *
	 * @return string
	 */
	function getName() {
		return 'my_extension';
	}
}
```

Once we have our extension, we must register it using the service manager:

```yaml
MyApp:
    CustomExtension:
        Class: \MyApp\Demo\MySmartyExtension
        Tags: [Smarty.Extension]
```

And that's it, we can now use the modifier in our templates:

```php
{'this is my name'|custom_upper}
// outputs: THIS IS MY NAME
```

Resources
---------

To run unit tests, you need to use the following command:

    $ cd path/to/Webiny/Component/TemplateEngine/
    $ composer.phar install
    $ phpunit