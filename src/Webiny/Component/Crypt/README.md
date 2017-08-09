Crypt Component
===============
The `Crypt` component provides methods for generating random numbers and strings, also, password hashing and password
hash verification and methods for encryption and decryption of strings. Internally it uses cryptographically secure methods.


**Disclaimer:**
The library was not reviewed by a security expert. 


Install the component
---------------------
The best way to install the component is using Composer. This library requires that you also add a repository to your
composer.json file.

```bash
composer require webiny/crypt
```
For additional versions of the package, visit the [Packagist page](https://packagist.org/packages/webiny/crypt).

## Using Crypt

```php
class MyClass
{
    use Webiny\Component\Crypt\CryptTrait;

    function myMethod()
    {
        $this->crypt()->encrypt('to encrypt', 'secret key');
    }
}
```

## Generate random integers

To generate a random integer you just have to pass the range to the `Crypt` instance:

```php
    $randomInt = $crypt->generateRandomInt(10, 20); // e.g. 15
```

## Generate random strings

When you want to generate random string, you have several options. You can call the general `generateRandomString` method,
or you can call `generateUserReadableString` method to get a more user-readable string that doesn't contain any special
characters. There is also a method called `generateHardReadableString` that, among letters and numbers, uses special
characters to make the string more "harder".
Here are a few examples:

```php
    // generate a string from a defined set of characters
    $randomString = $crypt->generateRandomString(5, 'abc'); // e.g. cabcc

    // generate a string that contains only letters (lower & upper case and numbers)
    $randomString = $crypt->generateUserReadableString(5); // A12uL

    // generate a string that can contain special characters
    $randomString = $crypt->generateHardReadableString(5); // &"!3g
```

## Password hashing and validation


```php
    // hash password
    $passwordHash = $crypt->createPasswordHash('login123'); // $2y$08$GgGha6bh53ofEPnBawShwO5FA3Q8ImvPXjJzh662/OAWkjeejAJKa

    // (on login page) verify the hash with the correct password
    $passwordsMatch = $crypt->verifyPasswordHash('login123', $passwordHash); // true or false
```

## Encrypting and decrypting strings


```php
    // encrypt it
    $encrypted = $crypt->encrypt('some data', 'abcdefgh12345678');

    // decrypt it
    $decrypted = $crypt->decrypt($result, 'abcdefgh12345678'); // "some data"
```

## Crypt config

There are three different internal crypt libraries that you can choose from:
 1. **OpenSSL** - this is the default library
 2. **Sodium** - library that utilizes [paragonie/halite](https://github.com/paragonie/halite) internally for password hashing, password verification, encryption and decryption. Please note that this library is highly CPU intensive.
 3. **Mcrypt** - this is the **depricated** library which will be removed once we hit PHP v7.2

To switch between libraries, just set a different `Bridge` in your configuration:
```yaml
Crypt:
    Bridge: \Webiny\Component\Crypt\Bridge\Sodium\Crypt
```

and then in your code just call:
```php
\Webiny\Components\Crypt\Crypt::setConfig($pathToYourYaml);
```

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
