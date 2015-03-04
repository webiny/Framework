Crypt Component
===============
The `Crypt` component provides methods for generating random numbers and strings, also, password hashing and password
hash verification and methods for encryption and decryption of strings.

The component uses a combination of three different seeds for providing randomness:
  - MCRYPT_DEV_URANDOM,
  - mt_rand
  - microtime

For mixing seeds we use a basic combination of `mt_rand`, `shuffle` and `str_shuffle`.

Password hashing and validation if done using native `password_hash` and `password_verify` methods.
Encoding and decoding is done using `mcrypt_encrypt` and `mcrypt_decrypt` methods.

Notice:
This class will provide the necessary security for most your day-to-day operations, like
storing and verifying passwords, generating random numbers and strings,
and also basic encryption and decryption.

The library has been tested, but not reviewed by a security expert. If you have
any suggestions or improvements to report, feel free to open an issue.

If you require a more advanced library for generating higher strength random numbers,
we suggest you use, or create a driver for, [ircmaxell/RandomLib](https://github.com/ircmaxell/RandomLib).


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

A preferred way of storing users passwords in a database is by hashing/encrypting it first. You can use common hashing
algorithms like `md5` or `sha1`, but a more secure way is using encryption algorithms like Blowfish.
This component comes with a support for encrypting and validating passwords using such a method.
Note that you don't need to add `salt` to your password, the salt is handled internally in the `createPasswordHash` method.


```php
    // hash password
    $passwordHash = $crypt->createPasswordHash('login123'); // $2y$08$GgGha6bh53ofEPnBawShwO5FA3Q8ImvPXjJzh662/OAWkjeejAJKa

    // (on login page) verify the hash with the correct password
    $passwordsMatch = $crypt->verifyPasswordHash('login123', $passwordHash); // true or false
```

## Encrypting and decrypting strings

The last feature provided by this component is encryption and decryption of strings. This process uses a secret key and
a initialization vector (http://en.wikipedia.org/wiki/Initialization_vector). The IV is handled internally, within the method.
The provided key needs to be exactly the same for the decryption process as is was for the encryption process,
or else the string cannot be decrypted back to its original form. In that case, the component returns `false` as the result.

```php
    // encrypt it
    $encrypted = $crypt->encrypt('some data', 'abcdefgh12345678');

    // decrypt it
    $decrypted = $crypt->decrypt($result, 'abcdefgh12345678'); // "some data"
```

# Crypt config

The component doesn't take any configuration.
Internally the following values are set.

Password algorithm: `CRYPT_BLOWFISH`
- used for generating password hashes

Encryption cipher: `rijndael-128`

Encryption mode: `cfb`
- used for encryption and decryption


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
