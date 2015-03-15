CHANGELOG
=========

1.3
--------
* Code is now fully written PSR-2 coding standard
* The Crypt component, inside the token and encoder, is now used directly, without the ServiceManager
* Encoders and Tokens now have a default internal config, so you don't need to defined them in your config
* Added "short names" for built-in auth and user providers
* All user providers are used by default
* Token and Encoder no longer need to be defined in the Firewall config section
* Replaced the default '_null' encoder with 'Default' encoder that uses the Crypt driver
* Encoder no longer uses salt, it's handled internally by the Crypt component
* MemoryProvider class renamed to Memory, so if follows the syntax of other providers
* Reduced the token cookie size by 50%
* TokenKey (Encoder.SecretKey) is now hashed and permuted per-user, making the token more secure
* Added 'false' option for a firewall encoder. In that case, the Null encoder is used.

1.2
---------
* Entity attribute access via magic getter now returns the actual attribute value and not attribute instance. To access attribute instance use `getAttribute($name)` method.
* EntityAbstract class now has a `findOne($criteria = [])` method to return single entity instance by search criteria.
* Mongo config no longer requires `ResultClass` parameter
* Security now provides a shorter access to firewall: instead of `$this->security()->firewall('admin');` we can now use `$this->security('admin')`
* `createPasswordHash($password)` method is now exposed in a Firewall: `$this->security('admin')->createPasswordHash($password);`
* `json_encode()` now triggers `__toString()` magic method on EntityAbstract
* ArrayObject now has a `mergeSmart()` method which works exactly as ConfigObject mergeWith() algorithm. 

1.0.0
---------
* Initial version`