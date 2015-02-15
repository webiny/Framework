Mailer Component
================

The `Mailer` component enables you to send emails using different supported protocols.

Install the component
---------------------
The best way to install the component is using Composer.

```bash
composer require webiny/mailer
```
For additional versions of the package, visit the [Packagist page](https://packagist.org/packages/webiny/mailer).

## Usage

Current supported protocols are:

* SMTP (you can also use Mandrill and Sendgrid's via their SMTP API)
* PHPs' `mail()` function
* Sendmail
* Mandrill ([Mandrill API Docs](https://mandrillapp.com/api/docs/messages.php.html#method=send))
* Sendgrid ([Sendgrid API Docs](https://sendgrid.com/docs/API_Reference/Web_API/mail.html))

To use the component, you first need to configuration set inside the component config file.
If you open the `ExampleConfig.yaml` you can see two example configuration sets, `Demo` and `Gmail`.
Follow this example to create your own set.

Here is an example configuration:

```yaml
    Mailer:
        Default:
            CharacterSet: utf-8
            MaxLineLength: 78
            Priority: 2
            Sender:
                Email: nikola@tesla.com
                Name: Nikola Tesla
            Transport:
                Type: smtp
                Host: smtp.gmail.com
                Port: 465
                Username: me@gmail.com
                Password: ***
                Encryption: ssl
                AuthMode: login
            AntiFlood:
                Threshold: 99
                Sleep: 1
            DisableDelivery: false
        Mandrill:
            Mode: template # template or html
            ApiKey: yourApiKey
            DisableDelivery: false
            Message:    # these are all optional
                FromEmail: ''
                FromName: ''
                Headers: []
                Important: false
                TrackOpens: null
                TrackClicks: null
                AutoText: null
                AutoHtml: null
                InlineCss: null
                UrlStripQs: null
                PreserveRecipients: null
                ViewContentLink: null
                BccAddress: ''
                TrackingDomain: null
                SigningDomain: null
                ReturnPathDomain: null
                Merge: true
                MergeLanguage: mailchimp
                Tags: []
                Subaccount: null
                GoogleAnalyticsDomains: []
                GoogleAnalyticsCampaign: ''
                Metadata: []
                RecipientMetadata: []
                Attachments: []
        Sendgrid:
            ApiUser: yourApiUser
            ApiKey: yourApiKey
            DisableDelivery: false
            Decorators:
                Wrapper: ['*|', '|*']
```

You can have unlimited configuration sets.

To register the config with the component, just call `Mailer::setConfig($pathToYamlConfig)`.

Depending on defined `Transport.Type` other transport parameters are required.

## Configuration parameters

The `Mailer` configuration consists of several parameters that are explained in the next few sections.

**Note:** Some of the configuration parameters are bridge-specific, like the `AntiFlood` parameter. The default bridge is the **SwiftMailer**, which of course supports the AntiFlood measures.

### Character set (`CharacterSet`)

This is the default character set that will be used in encoding your email content.
By default the character set is set to `utf-8` which supports most language characters.
You might need to change this for some languages, for example, like Japanese.

### Sender (`Sender`)

This is the default sender that will be set on your outgoing emails.

### Transport (`Transport`)

The transport configuration block consists of following parameters: 

- `Type`
    - defines the type of the connection
    - can be `smtp`, `mail` or `sendmail`


These parameters are needed only in case of a SMTP connection:

- `Host`
    - defines the location of your smtp host
- `Port`
-   - the port used to connect to the host
    - port can vary based on the defined `encryption` and `auth_mode`  
- `Username`
    - username needed to connect to the host
- `Password`
    - password needed to connect to the host
- `Encryption`
    - encryption used for the connection
    - this parameter is optional
    - can be `ssl` or `tls` based on your host
- `AuthMode`
    - authorization mode used to connect to the host
    - this parameter is optional
    - can be `plain`, `login`, `cram-md5`, or `null`

### AntiFlood (`AntiFood`)

Some mail servers have a set of safety measures that limit the amout of emails that you can send per connection or in some time interval. This is mostly to discourage spammers to user their services, but sometimes that might cause a problem even for non-spammers. In order to avoid falling into these safety measure the `AntiFood` parameter can limit how many emails you can send per connection and how much time you have to wait until you can establishe a new connection.

Don't worry about disconnecting, connecting again and resuming the sending of emails...this is all fully authomized and you don't have to do anything.

The `AntiFood` param consists of two attributes:
- `Threshold`
    - defines how many emails to send per one connection
- `Sleep`
    - defines how many seconds to wait until a new connection can be established and the sending resumed


## Usage

Using the `Mailer` component is quite simple, just implement the `MailerTrait`, build your message and send it.

Here is one simple usage example:

```php
class MyClass
{
    use \Webiny\Component\Mailer\Bridge\MailerTrait;

	function sendEmail() {
		// get the Mailer instance
		$mailer = $this->mailer('Default');

		// let's build our message
		$msg = $mailer->getMessage();
		$msg->setSubject('Hello email')
			->setBody('This is my test email body')
			->setTo(new Email('me@gmail.com', 'Jack'));

		// send it
		$mailer->send($msg);
	}
}
```

Now if you have multiple senders, and let's say you want to send all of them the same email, but just with a little difference,
for example that in each email you put the name of the specific user.

```php
class MyClass
{
	use \Webiny\Component\Mailer\Bridge\MailerTrait;

	function sendEmail() {
		// get the Mailer instance
		$mailer = $this->mailer('Default');


		// let's build our message
		$msg = $mailer->getMessage();
		$msg->setSubject('Hello email')
			 ->setBody('Hi {name},
						   This is your new password: <strong>{password}</strong>.')
			 ->setTo([
					new Email('jack@gmail.com'),
					new Email('sara@gmail.com')
					]);

		// before sending, let's define the decorator replacements
		$replacements = [
			'jack@gmail.com' => [
				'name'     => 'Jack',
				'password' => 'seCre!'
			],

			'sara@gmail.com' => [
				'name'     => 'Sara',
				'password' => 'Log!n'
			]
		];
		$mailer->setDecorators($replacements);

		// send it
		$mailer->send($msg);
	}
}
```

## Bridge

The default bridge library is `SwiftMailer` (http://swiftmailer.org/).

If you wish to create your own driver ,you need to create three classes:

- **Message**
    - this class should implement `\Webiny\Component\Mailer\Bridge\MessageInterface`
    - this class is used for populating message attributes, like sender, body ...
- **Transport**
    - this class should implement `\Webiny\Component\Mailer\Bridge\TransportInterface`
    - it is used for sending the message
- **Mailer**
    - this class should implement `\Webiny\Component\Mailer\Bridge\MailerInterface`
    - this class has only two methods, one returns an instance of Message and the other an instance of Transport

Resources
---------

To run unit tests, you need to use the following command:

    $ cd path/to/Webiny/Component/Mailer/
    $ composer.phar install
    $ phpunit