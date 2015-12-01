CHANGELOG
=========

1.4
---------
* added DynamicAttribute
* added ObjectAttribute (empty associative array is saved as native JS object, not array)
* added onSetNull() and setUpdateExisting() on Many2OneAttribute
* added setSkipOnPopulate(), onSet(), onGet(), onToArray() and onToDb() callbacks on all attributes 
* added support for Entity attribute validation and custom validators through Entity config
* ArrayAttribute now supports nested key validation and validation messages

1.0.0-RC1
---------
* Initial version