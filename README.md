Webiny Framework
================

This is a set of components for building PHP applications. Each of the component has its own documentation with usage examples and examples of configuration.

Install the framework
---------------------
The best way to install the framework is using Composer.

```bash
composer require webiny/framework
```
For additional versions of the package, visit the [Packagist page](https://packagist.org/packages/webiny/framework).

## Requirements

Webiny Framework requires PHP 5.5 or later.

## Feedback

We do love feedback, it doesn't matter if it's positive or not, any feedback is much appreciated.
So if you have something to tell us, please email us at **info{at}webiny.com**.

## Licence

Webiny Framework is released under MIT license.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 
## Some notes on coding:
- before writing any code, make sure you have read PSR-1 coding standard (http://www.php-fig.org/psr/psr-1/)
- each package should have its own exception handler
- prefer the usage of 'use' keyword instead of writing the full class name with namespace

### Git & IDE Configuration
**Line endings**
PHPStorm > File > Line Separators > LF
PHPStorm > Preferences > Code Style > General > line separator (for new files) > Unix

**Git**
Execute in terminal:
git config --global core.autocrlf input

## Bridges and Components

### Bridges

Webiny Framework is written in a way that it maximally re-uses other open-source components, so that we don't write the same code over and over again. But in order to make some certain compatibility layer between our components and 3rd party libraries, we introduced **Bridges**. If a component uses a 3rd party library, it is used over a bridge, where we implement an interface, so if we wish to change the external library, we would just create a new bridge, without the need to refactor the component itself.

### Components

This is the list of currently available components:
- [Amazon](src/Webiny/Component/Amazon)
    - currently supports implementation of Amazon S3
- [Annotations](src/Webiny/Component/Annotations)
    - component for parsing annotations from a `class`, `method` or a `property`
- [Bootstrap](src/Webiny/Component/Bootstrap)
    - MVC bootstrap component
- [Cache](src/Webiny/Component/Cache)
    - provides several caching libraries like Apc, Couchbase, Memcache and Redis
- [ClassLoader](src/Webiny/Component/ClassLoader)
    - a PSR-0, PSR-4 and PEAR class loader
- [Config](src/Webiny/Component/Config)
    - a very handy library for parsing YAML, INI, JSON and PHP configuration files
- [Crypt](src/Webiny/Component/Crypt)
    - library for encoding, decoding and validating hashes
- [Entity](src/Webiny/Component/Entity)
    - MongoDb ODM layer
- [EventManager](src/Webiny/Component/EventManager)
    - want to do event-based development, this is a library for you
- [Http](src/Webiny/Component/Http)
    - library for parsing all data from an HTTP request
    - will soon also support building an HTTP response
- [Image](src/Webiny/Component/Image)
    - library for image manipulation
- [Logger](src/Webiny/Component/Logger)
    - a component for handling logging during code execution
- [Mailer](src/Webiny/Component/Mailer)
    - component for sending emails
- [Mongo](src/Webiny/Component/Mongo)
    - MongoDB class wrapper
- [OAuth2](src/Webiny/Component/OAuth2)
    - library for working with OAuth2, currently supports Facebook, LinkedIn and Google+
- [REST](src/Webiny/Component/Rest)
    - fully featured REST library with caching, security and rate control
- [Router](src/Webiny/Component/Router)
    - handles defining, parsing, creating and matching url routes
- [Security](src/Webiny/Component/Security)
    - provides authorization and authentication layer
    - supports Http, Web form, Twitter and OAuth2 authentication
- [ServiceManager](src/Webiny/Component/ServiceManager)
    - want to write truly service based, loosely-coupled code, this library provides that
- [StdLib](src/Webiny/Component/StdLib)
    - tired of constantly mixing legacy PHP functions and objective code
    - this component provides objective wrappers for Arrays, Strings, Urls, Files, and DateTime types
- [Storage](src/Webiny/Component/Storage)
    - storage abstraction layer that simplifies the way you work with files and directories
    - supports local file system and Amazon S3
- [TemplateEngine](src/Webiny/Component/TemplateEngine)
    - provides a layer for rendering view templates and defining template plugins and manipulators
- [TwitterOAuth](src/Webiny/Component/TwitterOAuth)
    - library for working with Twitter API using Twitter OAuth

## Unit testing
All of the components feature unit tests, but some cover more code, while others cover only a small portion. We intend to change that over time and to have as much as possible of our code covered by unit tests.

To run the unit tests, you need to use the following command:

    $ cd path/to/vendor/webiny/framework/
    $ composer.phar install
    $ phpunit

Note that some components like, `Cache`, `Mailer` and `Storage` might require that you update their test configuration
before running the unit tests. Checkout the component readme file for more information.
