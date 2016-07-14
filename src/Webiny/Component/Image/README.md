Image Component
===============

The `Image` component provides basic functions for manipulating images. Component also fixes the incorrect rotation 
on pictures taken by a smartphone like iPhone or Android.

Install the component
---------------------
The best way to install the component is using Composer.

```bash
composer require webiny/image
```
For additional versions of the package, visit the [Packagist page](https://packagist.org/packages/webiny/image).

## Usage

This component is deeply coupled with the `Storage` component,
so it's advised that you get yourself familiar with that component first, if you already haven't.

Under **basic functions** it's meant that you can do these manipulations on any image:
- `resize`
- `crop`
- `rotate`

But before you can do any of the manipulations, you must first load an image. You can load an image from several different
sources by calling appropriate methods on `ImageLoader` class:
- `create` - creates a blank image
- `open` - create an image from the given File storage instance
- `load` - create an image from the given binary string
- `resource` - create an image from the given (stream) resource

Each of these methods returns an instance of `ImageInterface`. Using `ImageInterface` you can perform the defined manipulations
on the loaded image.

Once you are done manipulating the image you can either save it by calling the `save` method, or you can output the image
by calling the `show` method.

Here is a usage example:

```php
// storage
$imageStorage = \Webiny\Component\ServiceManager\ServiceManager::getInstance()->getService('storage.local');
$image = new \Webiny\Component\Storage\File\File('embed.jpg', $imageStorage);

// load the image using the `open` method
$imgInstance = \Webiny\Component\Image\ImageLoader::open($image);

// perform manipulations
$imgInstance->resize(800, 800)
            ->crop(200, 200, 50, 40);
            ->rotate(30, 'bfbfbf');

// save the new image
$destination = new \Webiny\Component\Storage\File\File('embed-rotated.jpg', $imageStorage);
$result = $imgInstance->save($destination); // if you don't set the destination, the original image will be overwritten
```

## Using ImageTrait

An easier way of loading the image and creating instances of ImageInterface is using the ImageTrait.
Here is an example of loading the image from the \Webiny\Component\Storage\File\File object:

```php
class MyClass{
	use \Webiny\Component\Image\ImageTrait;

	function __construct(){
		$imageStorage = \Webiny\Component\ServiceManager\ServiceManager::getInstance()->getService('Storage.Local');
		$image = new \Webiny\Component\Storage\File\File('embed.jpg', $imageStorage);

		$imgInstance = $this->image($image);

		$imgInstance->resize(800, 800);
		$imgInstance->crop(200, 200, 50, 40);
		$imgInstance->rotate(30, 'bfbfbf');

		$imgInstance->show();
	}
}
```

Here is another example where we load the image by file storage key and storage driver:

``` php
class MyClass{
	use \Webiny\Component\Image\ImageTrait;

	function __construct(){
		$imgInstance = $this->image('embed.jpg', 'Local');

		$imgInstance->resize(800, 800);
		$imgInstance->crop(200, 200, 50, 40);
		$imgInstance->rotate(30, 'bfbfbf');

		$imgInstance->show();
	}
}
```

So as you can see, you can load and create ImageInterface instances either by passing directly the File object, or by
passing the file key together with the name of the storage service.

## Bridge

The default bridge for image library uses `Imagine` library (https://github.com/avalanche123/Imagine) by Bulat Shakirzyanov
which perform all the image manipulations.

If you wish to expand or change the current bridged library you need to create two classes:
- a loader class that implements `Webiny\Component\Image\Bridge\ImageLoaderInterface`
- an image manipulation class that extends `Webiny\Component\Image\Bridge\AbstractImage`

After that just change the `Bridge` in the configuration and the framework will use your bridge instead the
default one.

## Configuration

The `Image` component requires very little configuration. Here is an example:

```yaml
    Image:
        Library: gd
        Quality: 90
```

The `Library` parameter defines which image library will be used. Supported libraries by `Imagine` library are:
- `gd` - uses native PHPs GD library
- `imagick` - uses ImageMagick library which requires php-imagick extension
- `gmagick` - uses GraphicsMagick API which also requires php-gmagick extension

The `Quality` parameter defines at which quality to save the image. The default quality is 90 (max is 100).

To register the config with the component, just call `Image::setConfig($pathToYamlConfig)`.

Resources
---------

To run unit tests, you need to use the following command:

    $ cd path/to/Webiny/Component/Image/
    $ composer.phar install
    $ phpunit