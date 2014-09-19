OAuth2 Wrappers
==============

This component provides wrappers for several OAuth2 systems like Facebook, LinkedIn and Google.
After you have gained OAuth2 access token, you can use this wrapper to communicate with the the desired service.

Install the component
---------------------
The best way to install the component is using Composer.

```json
{
    "require": {
        "webiny/oauth2": "1.0.*"
    }
}
```
For additional versions of the package, visit the [Packagist page](https://packagist.org/packages/webiny/oauth2).

Once you have your `composer.json` file in place, just run the install command.

    $ php composer.phar install

To learn more about Composer, and how to use it, please visit [this link](https://getcomposer.org/doc/01-basic-usage.md).

Alternatively, you can also do a `git checkout` of the repo.


## Supported OAuth2 servers

Current supported OAuth2 servers are:

* Facebook
* Google
* LinkedIn

## Configuring the component

To use the component, you first need to configure it.
The configuration is done by defining the following params:

- **Server** - class that will be used to process the response from OAuth2 server
- **ClientId** - OAuth2 client id
- **ClientSecret** - OAuth2 client secret
- **Scope** - scope parameter based on the selected OAuth2 server
- **RedirectUri** - location where the user will be redirected by the OAuth2 server once he is authorized

### Example configuration:

```yaml
    OAuth2:
        Facebook:
            Server: \Webiny\Component\OAuth2\Server\Facebook
            ClientId: 273234862555915
            ClientSecret: fe5G55632eeabc2086f8209a3ff05g22
            Scope: email
            RedirectUri: '/security/login-fb/'
        GPlus:
            Server: \Webiny\Component\OAuth2\Server\Google
            ClientId: 716241231612.apps.googleusercontent.com
            ClientSecret: KyP8Eag3a60Jgb3mkgiuPFdZYl
            Scope: openid%20profile%20email
            RedirectUri: '/security/login-gp/'
```

## Usage

This component depends on users access token, without it no API call to the OAuth2 server can be made.
To get the access token, please read the implementation guide for a specific server you wish to use.
OAuth2 components is also integrated with the `Security` component as a user and authentication provider, automating the process of getting the required auth token.

Example:
```php
// load instance of `GPlus` configuration
$instance = OAuth2Loader::getInstance('GPlus');

// set access token
$instance->setAccessToken('...');

// do API requests to get user details
$userProfile = $instance->request()->getUserDetails();

// do an API request to a specific API method
$result = $instance->request()->rawRequest($url, $params);
```

## Registering additional servers

First create a class that extends `\Webiny\Component\OAuth2\ServerAbstract` and then implement the abstract methods.
All of the abstract methods are described inside `ServerAbstract` class, and additionally you should also check out how
implementations of current servers looks like. They are located in `\Webiny\Component\OAuth2\Server` folder.

```php
class Instagram extends \Webiny\Component\OAuth2\ServerAbstract
{
    public function getAuthorizeUrl(){
        // TODO: Implement _getUserDetailsTargetData() method.
    }

    public function getAccessTokenUrl(){
        // TODO: Implement _getUserDetailsTargetData() method.
    }

	protected function _getUserDetailsTargetData() {
		// TODO: Implement _getUserDetailsTargetData() method.
	}

	protected function _processUserDetails($result) {
		// TODO: Implement _processUserDetails() method.
	}

	public function processAuthResponse($response) {
		// TODO: Implement processAuthResponse() method.
	}
}
```

Once you have implemented your logic for the abstract methods, it's time to register the class with the OAuth2 component.
In order to do so, inside your config file, set the value of `Server` property to your newly created class.

```yaml
    OAuth2:
        Instagram:
            Server: \MyLib\OAuth2\Server\Instagram
            ...
```

And you're done!
To use it, just configure it the same way as the built in classes.

## Notice

The code on this component is not fully covered by unit test. Only main classes are tested, while tests for `Bridge` and `Server` still need to be written.

Resources
---------

To run unit tests, you need to use the following command:

    $ cd path/to/Webiny/Component/OAuth2/
    $ composer.phar install
    $ phpunit