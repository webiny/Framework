CHANGELOG
=========

1.4
---------
* refactored caching layer
* cache now supports multiple drivers
* removed cache file path from debug headers
* rest annotation can now be passed from parent class to a child class
* added resource naming option (`@rest.url`)

1.1
---------
* added `getData` method that returns the result from the service without outputting it
* added `getError` method that returns the current error report, or false if there are no errors
* added `initRest` method and better routing option

1.0.0-RC1
---------
* Initial version
