TwitterOAuth
============

This component provides is a wrapper for Twitter OAuth server.

Install the component
---------------------
The best way to install the component is using Composer.

```bash
composer require webiny/twitter-oauth
```
For additional versions of the package, visit the [Packagist page](https://packagist.org/packages/webiny/twitter-oauth).

## Configuring the component

To use the component, you first need to configure it.
The configuration is done by defining the following params:

- **ClientId** - Twitter client id
- **ClientSecret** - Twitter client secret
- **RedirectUri** - location where the user will be redirected by the OAuth server, once he is authorized

### Example configuration:

```yaml
    TwitterOAuth:
        MyTwitterApp:
            ClientOd: AJmIXgXfaasdasaULBmnygIiGA
            ClientSecret: JE5dpb0a891ciisasdMOu7ELF9SI0TazR3hDAirft0Y
            RedirectUri: /security/login-tw/
        Bridge: \Webiny\Component\TwitterOAuth\Bridge\TwitterOAuth\TwitterOAuth
```


### Dependency

The library requires `abraham/twitteroauth` TwitterOAuth component. The will be installed automatically if you install
TwitterOAuth Webiny component using composer, however, you will need to include this file somewhere in your application
before you can use the component `/path/to/abraham/twitteroauth/twitteroauth.php`.

## Usage

Once you have configured the component, its usage is fairly simple if you have an access token. If you don't have an access token, the best way to get one, is to authenticate the user using the `Security` component with TwitterOAuth as auth provider.

```php
$tw = TwitterOAuthLoader::getInstance('MyTwitterApp');

$tw->setAccessToken(...);

// get user details for current access token
$user = $tw->getUserDetails(); // returns TwitterOAuthUser object

// perform any other API operation
$tw->get($url, $params);
$tw->post($url, $params);
$tw->delete($url, $params);
```

Resources
---------

To run unit tests, you need to use the following command:

    $ cd path/to/Webiny/Component/TwitterOAuth/
    $ composer.phar install
    $ phpunit