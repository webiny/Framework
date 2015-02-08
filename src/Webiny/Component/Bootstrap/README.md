Bootstrap Component
===================

Bootstrap is the first piece of code your web application runs. It loads all system components, and then runs your application.
The component uses MVC application architecture. **Note** that Webiny Framework is not an MVC framework, rather it's a set
of modular components that you can use in various different application architectures, for example like HMVC and others.

However, we decided to create this component, so that it helps other developers, that are mostly familiar with MVC, to
use Webiny Framework in their projects.


Install the Component
---------------------
The best way to install the component is using Composer.


```bash
composer require webiny/bootstrap
```
For additional versions of the package, visit the [Packagist page](https://packagist.org/packages/webiny/rest).


## Requirements 

The component requires that you follow a specific file-folder structure.
 
A skeleton app can be found here [Bootstrap Skeleton App](http://github.com/Webiny/Bootstrap-SkeletonApp).

A more advanced demo application can be found here [Bootstrap Todo Demo App](https://github.com/Webiny/Tutorial-TodoApp/).


### Application Namespace

Once you have your structure in place, you need to set your desired application namespace inside the `Config/App.yaml` file.

```yaml
Application:
    Namespace: MySuperApp
```

The `Namespace` defines the class root namespace for your module.


### Modules

Every module is placed inside the `Modules` folder inside the skeleton app. The module name should be written in CamelCase, e.g. "MySuperAwesomeModule".

```
Modules/
    |- MySuperAwesomeModule/
        |- Controllers/
        |- Views/
```


### Controllers

The `Controllers` folder, which is inside your module folder, holds your controller classes. Controller name must also be written in CamelCase. Every controller class must `use` the `Webiny\Component\Bootstrap\ApplicationTraits\AppTrait` trait. 

```php
<?php

namespace MySuperApp\Modules\MySuperAwesomeModule\Controllers;

class Homepage
{
    use Webiny\Component\Bootstrap\ApplicationTraits\AppTrait;

    public function doSomethingAction()
    {
        // your code goes here
    }
}
```

#### `AppTrait`

The `AppTrait` gives you access to the `app` method, which then provides access to various helper methods.

```php
class Homepage
{
    use Webiny\Component\Bootstrap\ApplicationTraits\AppTrait;

    public function doSomethingAction()
    {
        // get absolute path
        $this->app()->getAbsolutePath();
        
        // get web path
        $this->app()->getWebPath();
    
        // assign data to the view
        $viewData = [
            'title' => 'This is a title'
        ];
        $this->app()->view()->assign($viewData);
    }
}
```

There are also couple of View helper methods:

```php
class Homepage
{
    use Webiny\Component\Bootstrap\ApplicationTraits\AppTrait;

    public function doSomethingAction()
    {
        $this->app()->view()
             ->setTitle('Webiny Todo App')
             ->setMeta('description', 'Webiny demo Todo application')
             ->appendStyleSheet('//maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css')
             ->appendScript('//code.jquery.com/jquery-2.1.1.min.js')
             ->appendScript('//maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js');
    }
}
```

These setter methods, have also a getter pair, for outputting the result:

```php
class Homepage
{
    use Webiny\Component\Bootstrap\ApplicationTraits\AppTrait;

    public function doSomethingAction()
    {
        // outputs 'Webiny Todo App'
        $this->app()->view()->getTitle();
        
        // outputs '<title>Webiny Todo App</title>' 
        $this->app()->view()->getTitleHtml();
        
        // outputs an array of scripts
        $this->app()->view()->getScripts();
        
        // outputs a list of scripts as a HTML tags
        $this->app()->view()->getScriptsHtml();
        
        //...and few other, checkout the View.php class inside the ApplicationClasses folder
    }
}
```

#### Controller Actions

Every controller exposes certain public method that can be accessed over url. The method name of these methods must end
with `Action` keyword. Eg `doSomethingAction`.


### Views

Every controller has it's own view folder, that holds the view templates for controller actions. The view folder name,
must match the controller class name.

```
Modules/
    |- MySuperAwesomeModule/
        |- Controllers/
            |- Homepage.php
            |- ProductSearch.php
        |- Views/
            |- Homepage/
                |- DoSomething.tpl
```

The requirements for the view template name are:
- written in CamelCase
- must match the action name of the controller
- should not contain `Action` at the end
- example `Homepage/DoSomething.tpl` matches the `doSomethingAction` method on the `Homepage` controller.

By default, the Bootstrap component uses the [TemplateEngine](../TemplateEngine/) component, which uses `Smarty` template engine.


## Environments and Configuration Files

Within the `Config` folder, you have the `Production` folder, which must always exist. This is the location from where 
the component reads the configuration files. However, you can have additional folders, alongside the production one, that 
 hold environment-specific configurations. The `Production` config files are **always** loaded, the additional env-specific
 config files, just overwrite the production config variables. 

To create an environment, you need to first define it inside the `Config/App.yaml` config file:

```yaml
Application:
    Namespace: MySuperAwesomeModule
    Environments:
        Production:
            ErrorReporting: off
        Development:
            Domain: http://www.myapp.local/
            ErrorReporting: on
            SomeCustomVar: varValue
```
 
The `Domain` parameter defines when a certain environment will be loaded. The environment name, defines the folder that
will hold the configuration files. You can have as many environments as you want. 

If the `Domain`, for the upper example, matches the current hostname, the component will first load all the config files,
from the `Production` folder, and then all the files from the `Development` folder, and then it will merge both configurations
into one. 

```
Config/
    |- Production/
        |- Router.yaml
        |- Mongo.yaml
    |- Development/
        |- Mongo.yaml 
```


### System Configurations

Almost every component within Webiny Framework, takes a configuration file. That file defines the initial component data,
and how the component should be constructed. 

The `Bootstrap` component handles this initialization process automatically. If you create a configuration file, with a 
name matching a Webiny Framework component, the component will be initialized upon the application boot time and will
 be immediately available for usage in your application code. 


Checkout the `Config` folder inside the [Bootstrap Todo Demo App](https://github.com/Webiny/Tutorial-TodoApp/tree/master/src/App/Config/Production).

## Routing

By default the component uses standard MVC routing. For example, a request looking like this:
`www.myapp.com/HelloWorld/Foo/sayHi/` would match the following:
- `HelloWorld`: module name
- `Foo`: controller name
- `sayHiAction`: method name

The upper url, can also we written in lowercase with hyphens `www.myapp.com/hello-world/foo/say-hi/`. This would match
the same module-controller-action. 

### Custom Routes

To define custom routes, just create a `Router.yaml` config file, inside your environment. This file should follow the
[Router Component](../Router/) setup. The Bootstrap component will automatically pick up all the defined routes
and do the matching. If a custom route is not matched, Bootstrap will do a fall-back to the default MVC router.

This is an example how a custom route should be defined.

```yaml
Router:
    Routes:
        StartPage:
            Path: /
            Callback:
                Class: Demo\Modules\StaticPages\Controllers\Homepage
                Method: displayHomepage
```


### Passing Parameters

If an action method, takes one or more parameters, you can pass them inside the url path.

For example, let's say you have the following action method:

```php
public function sayHiAction($name, $location='Planet Earth')
{
    echo 'Hi '.$name.', from '.$location;    
}
```

You can pass the parameters like this: `www.myapp.com/hello-world/foo/say-hi/Jack/`, which would output: 

`Hi Jack, from Planet Earth`

or like this: `www.myapp.com/hello-world/foo/say-hi/Jack/Hawaii`, which would output:

`Hi Jack, from Hawaii`


Resources
---------

To run unit tests, you need to use the following command:

    $ cd path/to/Webiny/Component/Bootstrap/
    $ composer.phar install
    $ phpunit
