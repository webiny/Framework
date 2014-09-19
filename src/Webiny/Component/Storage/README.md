Storage Component
=================

Storage Component is a storage abstraction layer that simplifies the way you work with files and directories.

Install the component
---------------------
The best way to install the component is using Composer.

```json
{
    "require": {
        "webiny/storage": "1.0.*"
    }
}
```
For additional versions of the package, visit the [Packagist page](https://packagist.org/packages/webiny/storage).

Once you have your `composer.json` file in place, just run the install command.

    $ php composer.phar install

To learn more about Composer, and how to use it, please visit [this link](https://getcomposer.org/doc/01-basic-usage.md).

Alternatively, you can also do a `git checkout` of the repo.

## Usage

You will need to use storage drivers to access different storage providers like local disk, Amazon, Rackspace, etc.

Webiny Framework provides `Local` and `AmazonS3` storage driver but using a set of built-in interfaces will help you to develop a new driver in no time.

The following driver interfaces are available:

* `DriverInterface` - main storage driver interface
* `TouchableInterface` - for drivers that support `touch` functionality (change of time modified)
* `SizeAwareInterface` - for drivers that can access file `size`
* `DirectoryAwareInterface` - for drivers that can work with directories
* `AbsolutePathInterface` - for drivers that can provide absolute file path (ex: /var/www/app/storage/myFile.txt)


## Configuring a storage service

The recommended way of using a storage is by defining a storage service. Here is an example of defining a service using `Local` and `AmazonS3` storage driver:

```yaml
Storage:
    # You can optionally specify parameters for use in services definitions
    Parameters:
        Storage.Class: \Webiny\Component\Storage\Storage
        Storage.S3DriverArgs:
            - yourAccessKeyID
            - yourSecretAccessKey
            - webiny # Bucket name
            - false # Date folder structure
    Services:
        LocalStorage:
            Class: %Storage.Class%
            Arguments:
                Driver:
                    Object: \Webiny\Component\Storage\Driver\Local\Local
                    ObjectArguments:
                        - /var/www/projects/webiny/Public/Uploads # Absolute root path
                        - http://admin.w3.com/Uploads # Web root path
                        - false # DateFolderStructure
                        - true # Create folder if it doesn't exist
        CloudStorage:
            Class: %Storage.Class%
            Arguments:
                Driver:
                    Object: \Webiny\Component\Storage\Driver\AmazonS3\AmazonS3
                    ObjectArguments: %Storage.S3DriverArgs%
            Tags: [cloud]

```

(This is just one way of defining a service. For detailed documentation on defining a service refer to `ServiceManager` component.)

## Using your new storage

To make use of local storage easier and more flexible, there are 2 classes: `\Webiny\Component\Storage\File\LocalFile` and `\Webiny\Component\Storage\Directory\LocalDirectory`. These 2 classes serve as wrappers so you never need to make calls to storage directly. Besides, they contain common methods you will need to perform actions on files and directories.

Let's take a look at how you would store a new file:

```php
// use StorageTrait

// Get your storage service. Storage name is part of the service name after 'storage.'
$storage = $this->storage('local');

// Create a file object with a key (file name) and a $storage instance
$file = new LocalFile('file.txt', $storage);

$contents = file_get_contents('http://www.w3schools.com/images/w3schoolslogoNEW310113.gif');
$file->setContents($contents);
```

After calling `setContents($contents)` the contents is written to the storage immediately and a `bool` is returned.


## Working with directories
Sometimes you need to read the whole directory, filter files by name, extension, etc. There is a special interface for this type of manipulation, `\Webiny\Component\Storage\Directory\DirectoryInterface`.

Since not all storage engines support directories, there is no generic implementation of this interface. There is, however, an implementation in form of `LocalDirectory` which works nicely with local file storage. There are 2 modes of reading a directory: `recursive` and `non-recursive`. 

* `Recursive` will read the whole directory structure recursively and build a one-dimensional array of files (directory objects are not created but their children files are returned). This is very useful when you need to read all files at once or filter them by name, extension, etc.
* `Non-recursive` will only read current directory and return both child `Directory` and `File` objects. You can then loop through child `Directory` object to go in-depth.


## Reading a directory (non-recursive mode)

```php
// Get your storage service
$storage = $this->storage('local');

// Create a directory object with a key (directory name), $storage instance
$dir = new Directory('2013', $storage);

// Loop through directory object
foreach($dir as $item){
    if($this->isInstanceOf($item, 'LocalDirectory')){
		// Do something with child LocalDirectory object
	} else {
		// Do something with LocalFile object
	}
}

```

NOTE: directory files are not being fetched from storage until you actually use the object.

## Filtering files (recursive mode)
```php
// Get your storage service
$storage = $this->storage('local');

// Read recursively
$dir = new Directory('2013', $storage, true);

// Get only PDF files
$pdfFiles = $dir->filter('*.pdf');

// Count ZIP files
$zipFiles = $dir->filter('*.zip')->count();

// Get files starting with 'log_'
$logFiles = $dir->filter('log_*');

// You can also pass the result of filter directly to loops as `filter()` returns a new Directory object
foreach($dir->filter('*.txt') as $file){
    // Do something with your file
}

```

NOTE: calling `filter()` does not change the original Directory object, but creates a new Directory object with filtered result, so once you've read the root directory you can filter it using any condition as many times as you need:

```php
// Get your storage service
$storage = $this->storage('local');

// Read recursively and don't filter
$dir = new Directory('2013', $storage, true);

// Now you can manipulate the whole directory 

// Get number of all ZIP files in the directory tree
$zipFiles = $dir->filter('*.zip')->count();

// Get number of all RAR files in the directory tree
$zipFiles = $dir->filter('*.rar')->count();

// Get number of all LOG files in the directory tree
$zipFiles = $dir->filter('*.log')->count();

// Now output all files in the directory without filtering them
foreach($dir as $file){
    echo $file->getKey();
}

```

## Deleting a directory 
Deleting a directory (this is done recursively) is as simple as:
```php
// Get your storage service
$storage = $this->storage('local');

// Get directory
$dir = new Directory('2013', $storage);

// This will delete the whole directory structure and file FILE_DELETED event for each file along the way
$dir->delete();

// If you don't want the events to be fired, pass as second parameter `false`:
$dir->delete(false);

```

## Storage events
There are 3 types of events fired when certain actions are performed on a File:

* `wf.storage.file_saved` (StorageEvent::FILE_SAVED) - fired after a file was saved successfully.
* `wf.storage.file_renamed` (StorageEvent::FILE_RENAMED) - fired after a file is renamed.
* `wf.storage.file_deleted` (StorageEvent::FILE_DELETED) - fired after a file is deleted.

All 3 events pass an instance of `\Webiny\Component\Storage\StorageEvent` to their event handlers. Once your handler gets executed, you can access the file objects using `$event->getFile()` method.

```php
class Test {
    use EventManagerTrait;
    
    public function index(){
    
        // Listen for StorageEvent::FILE_SAVED
        $this->eventManager()->listen(StorageEvent::FILE_SAVED)->handler(function(StorageEvent $event){
            // Get the file object
            $file = $event->getFile();
        });
    }
}

```


`StorageEvent::FILE_RENAMED` event also assigns a special property called `oldKey` to the event object, which holds the value of file key before renaming (this will help you update your database records, etc.):

```php
class Test {
    use EventManagerTrait;
    
    public function index(){
    
        // Listen for StorageEvent::FILE_RENAMED
        $this->eventManager()->listen(StorageEvent::FILE_RENAMED)->handler(function(StorageEvent $event){
        
            // Get the file object
            $file = $event->getFile();
            
            // $file now contains the file object with new key, and we need to get the old key
            $oldKey = $event->oldKey;
            $newKey = $file->getKey();
        });
    }
}
```

Resources
---------

To run unit tests, you need to use the following command:

    $ cd path/to/Webiny/Component/Storage/
    $ composer.phar install
    $ phpunit