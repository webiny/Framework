Crypt Component
===============
The `Crypt` component provides methods for generating random numbers and strings, also, password hashing and password
hash verification and methods for encryption and decryption of strings.

Install the component
---------------------
The best way to install the component is using Composer. This library requires that you also add a repository to your
composer.json file.

```json
{
    "repositories": [
		{
			"type": "vcs",
			"url": "https://github.com/ircmaxell/PHP-CryptLib"
		}
	],
    "require": {
        "webiny/crypt": "1.0.*"
    }
}
```
For additional versions of the package, visit the [Packagist page](https://packagist.org/packages/webiny/crypt).

Once you have your `composer.json` file in place, just run the install command.

    $ php composer.phar install

To learn more about Composer, and how to use it, please visit [this link](https://getcomposer.org/doc/01-basic-usage.md).

Alternatively, you can also do a `git checkout` of the repo.

## Generate random integers

To generate a random integer you just have to pass the range to the `Crypt` instance:

```php
    $crypt = new \Webiny\Component\Crypt\Crypt();
    $randomInt = $crypt->generateRandomInt(10, 20); // e.g. 15
```

## Generate random strings

When you want to generate random string, you have several options. You can call the general `generateRandomString` method,
or you can call `generateUserReadableString` method to get a more user-readable string that doesn't contain any special
characters. There is also a method called `generateHardReadableString` that, among letters and numbers, uses special
characters to make the string more "harder".
Here are a few examples:

```php
    $crypt = new \Webiny\Component\Crypt\Crypt();

    // generate a string from a defined set of characters
    $randomString = $crypt->generateRandomString(5, $chars = 'abc'); // e.g. cabcc

    // generate a string that contains only letters (lower & upper case and numbers)
    $randomString = $crypt->generateUserReadableString(5); // A12uL

    // generate a string that can contain special characters
    $randomString = $crypt->generateHardReadableString(5); &"!3g
```

## Password hashing and validation

A preferred way of storing users passwords in a database is by hashing/encrypting it first. You can use common hashing
algorithms like `md5` or `sha1`, but a more secure way is using encryption algorithms like Blowfish.
This component comes with a support for encrypting and validating passwords using such a method.

```php
    $crypt = new \Webiny\Component\Crypt\Crypt();

    // hash password
    $passwordHash = $crypt->createPasswordHash('login123'); // $2y$08$GgGha6bh53ofEPnBawShwO5FA3Q8ImvPXjJzh662/OAWkjeejAJKa

    // (on login page) verify the hash with the correct password
    $passwordsMatch = $crypt->verifyPasswordHash('login123', $passwordHash); // true or false
```

## Encrypting and decrypting strings

The last feature provided by this component is encryption and decryption of strings. This process uses a secret key and
a initialization vector (http://en.wikipedia.org/wiki/Initialization_vector). Both parameters must be exactly the same
for the decryption process as they were for the encryption process, or else the string cannot be decrypted back to its
original form.

```php
    $crypt = new \Webiny\Component\Crypt\Crypt();

    // encrypt it
    $encrypted = $crypt->encrypt('some data', 'abcdefgh12345678');

    // decrypt it
    $decrypted = $crypt->decrypt($result, 'abcdefgh12345678'); // "some data"
```

# Crypt config

Example config:

```yaml
Crypt:
    Services:
        MyServiceExplanation:
            Class: \Webiny\Component\Crypt\Crypt # path to crypt class
            Arguments: [{$passwordAlgo}, {$cipherMode}, {$cipherBlock}, {$initializationVector}] # service structure
        FooCryptService:
            Class: \Webiny\Component\Crypt\Crypt
            Arguments: [Blowfish, CCM, rijndael-128, _FOO_VECTOR]
```

## About

`Crypt` config has the following options:

### "{$passwordAlgo}"

The algorithm used for hashing passwords. Supported algorithms depend on the defined `Bridge` library.
The default library, `PHP-CryptLib`, supports:
- **BCrypt** - (*default*)
- **PBKDF1**
- **PBKDF2**
- **SHA256** - (crypt()'s implementation)
- **SHA512** - (crypt()'s implementation)
- **Schneier** (a PBKDF derivative)

### "{$cipherMode}"

This is the mode that will be used for encrypting and decrypting strings.
Following modes are supported by the default library:
- **CBC** - Encryption (Cipher Block Chaining) - (*default*)
- **CCM** - Encryption and Authentication (Counter Cipher Block Chaining)
- **CFB** - Encryption (Cipher FeedBack)
- **CTR** - Encryption (Counter)
- **ECB** - Encryption (Electronic CodeBook)
- **NOFB** - Encryption (Output FeedBack - Variable Block Size)


### "{$cipherBlock}"

`cipher_block` is the portable block cipher used, in combination with `cipher_mode` for the encrypt/decrypt method.
The following options are available:
- **aes-128**
- **aes-192**
- **aes-256**
- **rijndael-128** - (*default*)
- **rijndael-160**
- **rijndael-192**
- **rijndael-224**
- **rijndael-256**
- **des**
- **tripledes**

### "{$initializationVector}"

This option holds the default value for the initialization vector used in `encrypt` and `decrypt` methods.
The default value is `_FOO_VECTOR`.

## Crypt as a service

This is the preferred way to use the component.

To define a crypt services, you need to create your config file and set it using:

```php
    Crypt::setConfig($pathToYourConfig);
```

Then you can use is by getting the instance over the `CryptTrait`

```php
class MyClass
{
    use CryptTrait;

    function myMethod(){
        $crypt = $this->crypt('FooCryptService');
    }
}
```

An example config can be found under `ExampleConfig.yaml`.

## Custom `Crypt` driver

To create a custom `Crypt` driver, first you need to create a class that implements `\Webiny\Component\Crypt\Bridge\CryptInterface`.
Once you have implemented all the requested methods, you now need to change the `Bridge` path
inside your component configuration.

Resources
---------

To run unit tests, you need to use the following command:

    $ cd path/to/Webiny/Component/Crypt/
    $ composer.phar install
    $ phpunit