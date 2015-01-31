Security Component
==================

The security component is a layer that takes care of the authentication and authorization processes for you.

Install the component
---------------------
The best way to install the component is using Composer.

```bash
composer require webiny/security
```
For additional versions of the package, visit the [Packagist page](https://packagist.org/packages/webiny/security).

# About

Before we go into details, is important that you are familiar with the terms of authorization, authentication and
access control, if you are not, please read the following articles:

- http://www.cyberciti.biz/faq/authentication-vs-authorization/
- http://stackoverflow.com/questions/6556522/authentication-versus-authorization

If you what to know more:

- http://en.wikipedia.org/wiki/Authorization
- http://en.wikipedia.org/wiki/Authentication
- http://en.wikipedia.org/wiki/Access_control

# Usage

NOTE: There are 2 ways of accessing your firewalls.

The long way:
```php
$firewall = $this->security()->firewall('admin');
```

And the short way:
```php
$firewall = $this->security('admin');
```

The usage of the component is fairly simple:

First you process the user login:
```php
class MyClass
{
    use SecurityTrait;

    function loginUser()
    {
        $loginSuccessful = $this->security('admin')->processLogin();
    }
}
```

Then you can play around with the authorization methods:
```php
class MyClass
{
    use SecurityTrait;

    function myMethod(){
        // get authenticated user
        $user = $this->security('admin')->getUser();

        // check if user has a role
        if($user->hasRole('ROLE_EDITOR')) {
            // user has role ROLE_EDITOR or any role with greater access level
        }

        // check if current user can access the current url
        if($this->security('admin')->isUserAllowedAccess()){
            // user can access the current url based on the defined access rules
        }
    }
}
```

If you wish to logout the user:
```php
class MyClass
{
    use SecurityTrait;

    function logoutUser()
    {
        $logoutSuccessful = $this->security('admin')->processLogout();
    }
}
```

# Example configuration

This is an example configuration of the security layer.
The next few topics will describe every part of the configuration.

```yaml
Security:
    Tokens:
        SomeTokenName:
            Driver: '\Webiny\Component\Security\Token\CryptDrivers\Crypt\Crypt'
            Params: [Cookie]
            SecurityKey: $3cR3tK3y_654321 # secret key that will be used to encrypt the token data
    Encoders:
        CryptEncoder:
            Driver: '\Webiny\Component\Security\Encoder\Drivers\Crypt'
            Params: [Password]
            Salt: 'CHANGE THIS SECRET' # must be 8, 16, 32 or 64 characters
    UserProviders:
        SomeOAuth2Provider:
            Driver: '\Webiny\Component\Security\User\Providers\OAuth2\OAuth2Provider'
        TwitterOAuthProvider:
            Driver: '\Webiny\Component\Security\User\Providers\TwitterOAuth\TwitterOAuth'
        SomeBuiltInMemoryProvider:
            john: {password: secret, roles: 'ROLE_USER'}
            admin: {password: login123, roles: 'ROLE_SUPERADMIN'}
        FromDatabase:
            Driver: '\Webiny\Component\Security\User\Providers\Entity\Entity'
            Params:
                Entity: 'My\App\Entities\User'
                Username: username
                Password: password
                Role: ROLE_USER
    AuthenticationProviders:
        Http:
            Driver: '\Webiny\Component\Security\Authentication\Providers\Http\Http'
        Facebook:
            Driver: '\Webiny\Component\Security\Authentication\Providers\OAuth2\OAuth2'
            Params:
                Server: Facebook # which OAuth2 server to use (defined under OAuth2 component)
                Roles: [ROLE_USER] # which role to assign to user authenticated with this provider
        TwitterOAuth:
            Driver: \Webiny\Component\Security\Authentication\Providers\TwitterOAuth\TwitterOAuth'
            Params:
                Server: MyTwitterApp # which twitter app to use (must be registered by TwitterOAuth component)
                Roles: [ROLE_USER] # which role to assign to user authenticated with this provider
    Firewalls:
        Admin:
            RealmName: Administration
            Anonymous: true
            RememberMe: true
            Token: SomeTokenName
            Encoder: MockEncoder
            UserProviders: [MockProvider]
            AuthenticationProviders: ['Http', 'Facebook']
            AccessControl:
                Rules:
                    - {Path: '/^\/[a-zA-Z0-9-_]+\/[a-zA-Z0-9-_]+\/[a-zA-Z0-9]{13}$/', Roles: ROLE_ANONYMOUS}
                    - {Path: '/^\/about/', Roles: ROLE_ANONYMOUS}
                    - {Path: '/^\/statistics/', Roles: ROLE_ANONYMOUS}
                DecisionStrategy: affirmative
            RoleHierarchy:
                ROLE_USER: ROLE_EDITOR
                ROLE_ADMIN: ROLE_USER
```

# Components

The security layer is actually a set of several components that work and communicate together. Each of these components
needs to be configured.

The next sections will go through the components and explain what they do.

## Tokens (`Security.Tokens`)

Tokens are used to encrypt user data and save it into the session or cookie. Using tokens, once the user is authorized, we can do all future authorizations over the token, no need to use any authentication providers, check the database and things like that. Tokens save a lot of processing time.

Every token has two configuration parameters:
- `Driver`: name of a registered `Crypt` service
- `Params`: list of parameters that are being passed to the `Driver` constructor
- `SecurityKey`: key that will be used to encrypt token data; key must a length of 2n (8, 16, 32, 64 characters)

Example token definition:
```yaml
Security:
    Tokens:
        SomeTokenName:
            Driver: '\Webiny\Component\Security\Token\CryptDrivers\Crypt\Crypt'
            Params: [Cookie]
            SecurityKey: $3cR3tK3y # secret key that will be used to encrypt the token data
```

The built-in token driver is using the `Crypt` component, which is probably satisfying for the most cases.
You can also have multiple tokens defined, with different drivers or security key, but you can only use one token per firewall.

## Encoders (`Security.Encoders`)

Encoders are services that are responsible for two things, creating a password hash from the provided string and
verifying if the submitted password matches the given hash. Encoders do the similar thing like tokens, but tokens do encryption of user data, which can be decrypted, while encoders create hashes, which is not a reversible process, so you cannot get the original string.

The encoder component comes with a default `Crypt` driver, that uses the built in `Crypt` component, for hashing and verifying passwords.

The driver requires that you have `Crypt` service defined. Just provide the name of the  service under `Params` and your encoder is ready. It is also very recommended that you define a `Salt` parameter that will be used to additionally secure the passwords.

Example encoder configuration:

```yaml
# encoder configuration
Security:
    Encoders:
        EncoderOne:
            Driver: \Webiny\Component\Security\Encoder\Drivers\Crypt
            Params: [Password]
            Salt: 'CHANGE THIS SECRET'
        EncoderTwo:
            Driver: \Webiny\Component\Security\Encoder\Drivers\Null
```

To create a custom encoder driver, you need to create a class that implements
`\Webiny\Component\Security\Encoder\EncoderDriverInterface`.

The component also comes with a `Null` driver, which doesn't encode passwords, it keeps them in their plain format.

## User Providers (`Security.UserProviders`)

User providers are like a databases from where the `Security` component queries the users.
Each provider consists of 2 parts, a user provider, and the user class itself.
The provider part is responsible for loading users based on submitted login credentials, while the user object is responsible for verifying the submitted credentials against the loaded object from the provider.

There are three built-in user providers, the `Memory` provider, `OAuth2` provider and the `TwitterOAuth` provider.

### Memory provider

The `Memory` provider gives you the option to define users directly inside your configuration file, and it looks like this:

```yaml
Security:
    UserProviders:
        MyTestMemoryUsers:
            john: {Password: secret, Roles: ROLE_USER}
            admin: {Password: login, Roles: [ROLE_SUPERADMIN, ROLE_GOD]}
        MyOtherMemoryUsers:
            marco: {password: polo, roles: ROLE_ADMIN}
```

As you see, you can have multiple memory providers defined. Which one you wish to use, depends on how you define your firewall. You can use both of them, but we'll see more about that later.

**NOTE:**
Make sure that you set `encoder` to `false` on firewalls that are using `memory` or some other provider that contains
passwords in raw format, that is, where passwords are not encrypted.


### OAuth2 provider

The OAuth2 provider depends on the `OAuth2` component and it must be wrapped together with the authentication provider,
described in the later topics. Basically, this provider gives you the option to do user authentication using any OAuth2 server, like Facebook, Google, LinkedIn, and many more.

To configure the OAuth2 user provider you just need to set the path to the built-in driver:

```yaml
Security:
    UserProviders:
        SomeOAuth2Provider:
            Driver: '\Webiny\Component\Security\User\Providers\OAuth2\OAuth2Provider'
```

### TwitterOAuth provider

Unfortunately Twitter doesn't support the version 2 of OAuth protocol, just version 1, so we created a special TwitterOAuth provider. Its configuration is very similar to the OAuth2 user provider.

```yaml
Security:
    UserProviders:
        TwitterOAuthProvider:
            Driver: '\Webiny\Component\Security\User\Providers\TwitterOAuth\TwitterOAuth'
```

### Entity provider

This provider uses the Entity component which is tied to your database. 
 
```yaml
FromDatabase:
    Driver: '\Webiny\Component\Security\User\Providers\Entity\Entity'
    Params:
        Entity: 'My\App\Entities\User'
        Username: username
        Password: password
        Role: ROLE_USER
```

**Entity** parameter points your entity class.
**Username** defines the field name in the collection that holds the username.
**Password** same as the username, just points to the password field.
**Role** points either to the collection field holding the users role, or will be used as the role name, if the field doesn't exist.


### Custom user providers

To implement a custom user provider you need to create a class that implements
`\Webiny\Component\Security\User\UserProviderInterface`. And you need to create a user class that extends
`\Webiny\Component\Security\User\UserAbstract`. And that's it, all other details are described inside the interface and
the abstract class.

### Combining multiple user providers

Each firewall can use one or more user providers. You can combine them how it best fits your needs. We will discuss that in the later topics.

### Authentication providers (`Security.AuthenticationProviders`)

Authentication providers are ways of authenticating users.

This is an example configuration for an authentication provider:

```yaml
AuthenticationProviders:
    Http:
        Driver: '\Webiny\Component\Security\Authentication\Providers\Http\Http'
    Facebook:
        Driver: '\Webiny\Component\Security\Authentication\Providers\OAuth2\OAuth2'
        Params:
            Server: Facebook # which OAuth2 server to use (defined under OAuth2 component)
            Roles: [ROLE_USER] # which role to assign to user authenticated with this provider
    TwitterOAuth:
        Driver: \Webiny\Component\Security\Authentication\Providers\TwitterOAuth\TwitterOAuth'
        Params:
            Server: ['MyTwitterApp'] # which twitter app to use (must be registered by TwitterOAuth component)
            Roles: [ROLE_USER] # which role to assign to user authenticated with this provider
```

The configuration must have two parameters, the `Driver` param that defines which class to use to process the authentication,
and an optional `Params` that forwards the different parameters to the driver constructor.

Additional parameters might be required for some other auth providers.

There are also four built-in auth providers:

#### Http auth provider

This is the basic Http authentication.
Driver: `\Webiny\Component\Security\Authentication\Providers\Http\Http`

#### Form auth provider

Use this provider when you have a HTML login form for authenticating your users.
Driver: `\Webiny\Component\Security\Authentication\Providers\Form\Form`.

Your HTML form must have these fields:
- `username`
- `password`
- `rememberme` (optional; default: "")

#### OAuth2 auth provider

This provider uses the OAuth2 protocol and the `OAuth2` component. The supported OAuth2 servers are defined the by
the `OAuth2` component.
Driver: `\Webiny\Component\Security\Authentication\Providers\OAuth2\OAuth2`

This provider requires a bit more configuration, so here is an example:

```yaml
Facebook:
    Driver: '\Webiny\Component\Security\Authentication\Providers\OAuth2\OAuth2'
    Params:
        Server: Facebook # which OAuth2 server to use (must be defined under OAuth2 component configuration)
        Roles: [ROLE_USER] # which role to assign to user authenticated with this provider
```

Notice the two attributes inside the params section, the `Server` attribute points to the defined OAuth2 configuration,
while `Roles`param defines which roles will be assigned to users that are authenticated by this provider.

#### TwitterOAuth auth provider
This auth provider is very similar to the OAuth2 auth provider, just this one is designed to work with Twitter OAuth server.

Here is an example configuration:
```yaml
TwitterOAuth:
    Driver: \Webiny\Component\Security\Authentication\Providers\TwitterOAuth\TwitterOAuth'
    Params:
        Server: ['MyTwitterApp'] # which twitter app to use (must be registered by TwitterOAuth component)
        Roles: [ROLE_USER] # which role to assign to user authenticated with this provider
```

## Firewall (`Security.Firewalls`)

Firewall is the central component that controls the authentication layer.

You can have multiple sets of firewall. Each firewall consists of following parameters:
- `RealmName`
    - user readable name of the current firewall
- `Anonymous`
    - is anonymous access allowed behind this firewall or not
- `RememberMe`
    - do you want to remember the users credentials for a period of time or just the current session
    - if you wish to use the 'Remember me' feature on the page, this must be set to `true`
- `Encoder`
    - if your passwords are hashed (and they should be), place the name of your `Encoder` here
    - the encoder name must match the names under `Security.Encoders`
- `Token`
    - which token will the firewall use to encrypt user data
- `UserProviders`
    - these are the user providers that the firewall will use to ask for a user account
    - you can define an array of user providers, and the firewall will ask them one-by-one
- `AuthenticationProviders`
    - unlike user providers, that just return a user for the given username, authentication providers do the checking of user credentials
    - for example, authentication provider would ask the user to login over facebook, but the user provider, using facebook api, would retrieve the account
- `AccessControl`
    - set of urls and roles that are required to enter that area (you can find more information in the sections below)
- `RoleHierarchy`
    - defines the hierarchy of roles for the current firewall (you can find more information in the sections below)

### Access control

Access control is the central part that handles the authorization.
Inside access control you define a set of rules, where each rule consist of a `Path` and a list of `Roles` that are required for accessing that path.

```yaml
AccessControl:
    Rules:
        - {Path: '/^\/[a-zA-Z0-9-_]+\/[a-zA-Z0-9-_]+\/[a-zA-Z0-9]{13}$/', Roles: ROLE_ANONYMOUS}
        - {Path: '/^\/about/', Roles: [ROLE_USER,ROLE_EDITOR]}
        - {Path: '/^\/statistics/', Roles: ROLE_ANONYMOUS}
    DecisionStrategy: affirmative
```

If a rule is not matched, the built-in, `ROLE_ANONYMOUS`, will be returned as the required role to access that path.

### Voters

Access control also has internal mechanism called `Voters`. These are like a jury that can either vote

- `ACCESS ALLOWED`
- `ACCESS_DENIED`
- `ACCESS_ABSTAINED`

There are two built-in voters, the `AuthenticationVoter` that votes based on if user is authenticated or not, and there
is a `RoleVoter` that votes based on if user has the necessary role to access the current area.

The logic behind voters is created so you can extend it and add your own voters. For example you can create a voter
that either allows or denies access based on users IP address, like a black-list filter.

To create a custom voter you need to create a class that implements `\Webiny\Component\Security\Authorization\Voters\VoterInterface`.
After that, you need to create a service and tag it with `Security.Voter` tag.

```yaml
MyComponent:
    Services:
        MyVoter:
            class: \MyCustomLib\MyCustomVoter
            tags: [Security.Voter]
```

### Decision strategy

Decision strategy is the property that defines how the system will make its ruling, either to allow or deny access,
based on the votes for the voters.

There are three different strategies that can be applied:
- *unanimous* - all voters must vote `ACCESS_ALLOWED` to allow access
- *affirmative* - only one `ACCESS_ALLOWED` vote is enough to allow access
- *consensus* - majority wins (tie denies access)

## Role hierarchy (`Security.RoleHierarchy`)

This component is mostly self-explanatory, it defines the list of available roles and their hierarchy.

Here is an example:

```yaml
RoleHierarchy:
    ROLE_USER: ROLE_EDITOR
    ROLE_ADMIN: ROLE_USER
```

`ROLE_USER` will have access to all areas that require `ROLE_USER`, `ROLE_EDITOR` or `ROLE_ANONYMOUS`.
`ROLE_ADMIN` will have access to all areas that require `ROLE_ADMIN`, `ROLE_USER`, `ROLE_EDITOR` or `ROLE_ANONYMOUS`.

## Events

The component fires several events that you can subscribe to:

- `wf.security.login_invalid` fired when user submits invalid login credentials
- `wf.security.login_valid` fired when user submits valid login credentials
- `wf.security.role_invalid` fired when authenticated user tries to enter an area that requires a higher role than he currently has
- `wf.security.logout` fired when processLogout is called on a firewall
- `wf.security.not_authenticated` fired when a user tries to access an area for which he doesn't have a proper authorization level (role)

All these events pass an instance of `\Webiny\Component\Security\SecurityEvent`.

There are also some, user provider specific, events:
`OAuth2` user provider event:
- `wf.security.user.oauth2` fired when user is authenticated over OAuth2 provider

`TwitterOAuth` user provider event:
- `wf.security.user.twitter` fired when user is authenticated over Twitter OAuth provider

Each of those two events, returns a different class, for OAuth2 it's `Webiny\Component\Security\User\Providers\OAuth2\OAuth2Event` and for Twitter it's `Webiny\Component\Security\User\Providers\TwitterOAuth`. Both classes have two methods, one returns an object, containing different user information we manged to get from the OAuth(2) server. The other method returns an instance of the OAuth class, either TwitterOAuth or OAuth2, giving you direct access to the API and the access key.

Resources
---------

To run unit tests, you need to use the following command:

    $ cd path/to/Webiny/Component/Security/
    $ composer.phar install
    $ phpunit