Amazon Component
=================

Amazon component serves as a wrapper for Amazon Web Services SDK. Currently only S3 component is being used.

Install the component
---------------------
The best way to install the component is using Composer.

```json
{
    "require": {
        "webiny/amazon": "dev-master"
    }
}
```
For additional versions of the package, visit the [Packagist page](https://packagist.org/packages/webiny/amazon).
Optionally you can add `"minimum-stability": "dev"` flag to your composer.json.

Once you have your `composer.json` file in place, just run the install command.

    $ php composer.phar install

To learn more about Composer, and how to use it, please visit [this link](https://getcomposer.org/doc/01-basic-usage.md).

Alternatively, you can also do a `git checkout` of the repo.

Resources
---------
To run unit tests, you need to use the following command:

    $ cd path/to/Webiny/Component/Amazon/
    $ composer.phar install
    $ phpunit

Make sure that you also set your S3 API details in `Tests/AmazonS3Test.php` before running the tests.