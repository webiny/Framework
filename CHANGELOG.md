CHANGELOG
=========

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