CHANGELOG
=========

dev-master
-----------
* removed `add()` method from collection attributes, use simple array `attribute[]` syntax to add values
* collection attributes now always have normalized EntityCollection as their internal value
* EntityCollection now always internally converts values to instances of AbstractEntity 
* added possibility of overriding attribute classes via config

1.5
---------
* added built-in attribute validators
* added support for pluginable validators


1.4
---------
* added DynamicAttribute
* added ObjectAttribute (empty associative array is saved as native JS object, not array)
* added onSetNull() and setUpdateExisting() on Many2OneAttribute
* added setSkipOnPopulate(), onSet(), onGet(), onToArray(), setAfterPopulate() and onToDb() callbacks on all attributes 
* added support for Entity attribute validation and custom validators through Entity config
* ArrayAttribute now supports nested key validation and validation messages
* EntityDataExtractor now supports nested attributes grouping using brackets, eg: `team[name,members,owner[id,email]]`
* Alias can be given to EntityDataExtractor to format returned data, eg: `meta[lastChargeFailed@chargeFailed,type@custom.typeFailed]`
* Changed default attributes returned by `toArray()`: Many2OneAttribute, ArrayAttribute and ObjectAttribute are no longer returned.


1.0.0-RC1
---------
* Initial version