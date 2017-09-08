Config Drivers
=====================

Config drivers are used by `ConfigObject` to parse config data, and also save it to file.
These are the drivers currently provided by Webiny:

- Ini
- Json
- Yaml
- PHP

If you are interested in developing a new config driver, you must extend the `AbstractDriver` class. It will have you implement the following methods:
```php
class MyCustomDriver extends AbstractDriver
{
    /**
     * Get config data as string
     *
     * @return string
     */
    protected function _getString() {
        // Implement
    }

    /**
     * Parse config resource and build config array
     * @return array
     */
    protected function _getArray() {
        // Implement
    }
}
```
By default, `AbstractDriver` class has built-in validation of driver resource and checks for string, `StringObject`, array, `ArrayObject` and `FileObject`.
If you want to handle the validation yourself, you will have to override the `_validateResource()` method:

```php
/**
 * Validate given config resource and throw ConfigException if it's not valid
 * @throws ConfigException
 */
protected function _validateResource() {
    // Perform validation of $this->_resource

    // If valid:
    return true;

    // If invalid:
    throw new ConfigException('MyCustomDriver resource must be of type ... ');
}
```
After that you can use your custom driver in your calls to `Config` and `ConfigObject` class methods, for example:
```php
// Get ConfigObject
$myCustomDriver = new MyCustomDriver($pathToFile);
$config = Config::getInstance()->parseResource($myCustomDriver);

// Get ConfigObject as string
$configString = $config->getAs($myCustomDriver);
```