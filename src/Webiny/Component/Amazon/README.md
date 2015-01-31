Amazon Component
=================

Amazon component serves as a wrapper for Amazon Web Services SDK. Currently only S3 component is being used.

Install the component
---------------------
The best way to install the component is using Composer.

```bash
composer require webiny/amazon
```
For additional versions of the package, visit the [Packagist page](https://packagist.org/packages/webiny/amazon).

Resources
---------
To run unit tests, you need to use the following command:

    $ cd path/to/Webiny/Component/Amazon/
    $ composer.phar install
    $ phpunit

Make sure that you also set your S3 API details in `Tests/AmazonS3Test.php` before running the tests.