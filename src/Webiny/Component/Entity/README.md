Entity Component
=====================
`Entity` component is an ORM layer for MongoDB. Entity classes created using this component will serve as the main building blocks for modules, forms, tables, etc.
Every entity attribute is a complex data type. There are no simple strings or integers. Even `boolean` is a complex data type and has a separate class. This allows us to
generate a lot of code and interface elements automatically.

Install the component
---------------------
The best way to install the component is using Composer.

```json
{
    "require": {
        "webiny/entity": "dev-master"
    }
}
```
For additional versions of the package, visit the [Packagist page](https://packagist.org/packages/webiny/entity).
Optionally you can add `"minimum-stability": "dev"` flag to your composer.json.

Once you have your `composer.json` file in place, just run the install command.

    $ php composer.phar install

To learn more about Composer, and how to use it, please visit [this link](https://getcomposer.org/doc/01-basic-usage.md).

Alternatively, you can also do a `git checkout` of the repo.

## Supported attributes
- `boolean`
- `char`
- `text`
- `integer`
- `float`
- `datetime`
- `date`
- `select`
- `array`
- `many2one`
- `one2many`
- `many2many`

`one2many` and `many2many` attributes extend `CollectionAttributeAbstract` class. These 2 attributes are the most complex because their value
is represented by `EntityCollection` class, which is a wrapper for actual array of data returned from database. This wrapper allows us to implement lazy loading
and provide simple interface for counting data in result set (per page and total).

`EntityAbstract` class, which operates on the database, is always returning instances of `EntityAbstract` class.

## Entity structure

An example code for entity structure:

```php
use Webiny\Component\Entity\EntityAbstract;

class Page extends EntityAbstract
{
    protected static $_entityCollection = "Page";

	protected function _entityStructure() {

		// Create attributes
		$this->attr('title')
				->char()
			->attr('author')
				->many2one()
					->setEntity('Author')
			->attr('status')
				->char()
					->setDefaultValue('draft')
			->attr('comments')
				->one2many('page')
					->setEntity('Comment')
					->setOnDelete('cascade')
		    ->attr('approvedComments')
                ->one2many('page')
                    ->setEntity('Comment')
                    ->setFilter(['status' => 'approved']);
			->attr('labels')
				->many2many('Page2Label')
					->setEntity('Label');
		    ->attr('createdOn')
                ->datetime()
                    ->setDefaultValue('now')
            ->attr('modifiedOn')
                ->date()
                    ->setDefaultValue('now')
                    ->setAutoUpdate(true)
            ->attr('metaData')
                ->arr()
                    ->setDefaultValue(['settings' => []]);

	}
}
```

Attributes `many2one`, `one2many` and `many2many` are lazy loaded, which means, the actual values of these attributes is not loaded from database until you try to access it.

## Access to attributes
To access attribute values you can use the following 2 approaches:
```php
// Long syntax
$page = Page::findById("53712ed46803fa4e058b456b");
$title = $page->getAttribute('title')->getValue();

// Short syntax
$page = Page::findById("53712ed46803fa4e058b456b");
$title = $page->title->getValue();
```

NOTE: you need to call `getValue()` to get the actual value of the attribute.
If you try to echo or concatenate attributes (not values, but the actual attributes), __toString magic method will be triggered which will automatically call `getValue()` method for you.

In case you are trying to access a value of a `many2one` attribute:
```php
$page = Page::findById("53712ed46803fa4e058b456b");
$authorName = $page->author->firstName . ' '. $page->author->lastName;
// $authorName will be equal to 'John Doe'
```

## Setting values
```php
// Recommended way (provides you with autocomplete on AttributeAbstract methods)
$page->getAttribute('title')->setValue('New title');

// or (no autocomplete is the only downside of this)
$page->title->setValue('New title');

// or - This will trigger `__set` magic method and call $page->getAttribute('title')->setValue('New title');
$page->title = 'New title';
```

## One2Many Attribute
This attribute's value is an instance of `EntityCollection`. You can you is in `foreach` loops, access values by numeric indexes and also call `count()` method to find out the total number of items in the data set.

```php
$page = Page::findById("53712ed46803fa4e058b456b");
echo $page->comments->count();

foreach($page->comments as $comment){
...
}
```


### Referential integrity
`one2many` attribute provides a way to control whether you want these records to be deleted with parent record, or prevent parent record from being deleted if it contains any child records. You can set it using `onDelete` method, and choose between `cascade` and `restrict`, accordingly. More options will be implemented if required.

### Aliases
This attribute also provides a way for create `aliases` for your data by linking to the same entity, but adding filter values. This means that when you access your attribute value, it will automatically apply the requested filter and only return linked entities that match the filter:

```php
->attr('approvedComments')
    ->one2many('page')
        ->setEntity('Comment')
        ->setFilter(['status' => 'approved']);
```
### Linking with entities
Say you posted a new comment, and you need to link it with the `Page` entity, you can do it using 2 approaches:

First one is to load a Page instance, and use `add()` method on the `one2many` attribute:
```php
// Load Page
$page = Page::findById("53712ed46803fa4e058b456b");

// Create new Comment
$comment = new Comment();
$comment->populate($commentData);

// Assign and save Page
$page->comments->add($comment);
$page->save();
```

Second approach is to set `Page` instance as a `Comment` property (the `page` property must exist in Comment entity as a `many2one` attribute):

```php
// Load Page
$page = Page::findById("53712ed46803fa4e058b456b");

// Create new Comment
$comment = new Comment();
$comment->populate($commentData);

// Set Page instance and save
$comment->page->setValue($page);
$comment->save();
```

Next time you load `Page` comments - the new `Comment` will be in the data set.

### ArrayAttribute
This attribute is mostly used for some extra data you want to store with your entity that is not shown to users in forms or grids (some settings, etc.).
The cool thing about this attribute is it's "set" and "get" methods, which allows you to get and set nested keys and get default value if some part of your key does not exist.

```php
// $page->settings is an instance of ArrayAttribute

// Set value by specifying whatever nesting level you want
// Even if some of the levels may not exist along the way - they will be created for you automatically
$page->settings->set('level1.level2.level3', 'My new value');

// Get value from key that may not exist
$page->settings->get('level1.level4', 'Default value');
```

### Convert EntityAbstract to array ###
You can get and array representation of current `EntityAbstract` instance by calling `toArray()` method.
By default, only simple and Many2One attributes will be included in the resulting array.
If you want to control which attributes to include, pass a string containing names of attributes. You can also control attributes of nested attributes:

```php
$data = $page->toArray('title,author.name,comments.text,comments.id,labels');
```

This will result in something like:
```php
Array
(
    [author] => Array
        (
            [name] => John Doe
        )

    [title] => First blog post
    [comments] => Array
        (
            [0] => Array
                (
                    [text] => Best blog post ever!
                    [id] => 53dee8d26803fafe098b4569
                )

        )

    [labels] => Array
        (
            [0] => Array
                (
                    [label] => marketing
                    [id] => 53dee8d26803fafe098b456b
                )

            [1] => Array
                (
                    [label] => seo
                    [id] => 53dee8d26803fafe098b456e
                )

        )

)
```

In case your entity has a lot of attributes, you can use '*' to specify 'all default attributes', and then add only specific attributes you need.
Default attributes are all attributes that are not `One2ManyAttribute` or `Many2ManyAttribute`. If you need to get `One2ManyAttribute` or `Many2ManyAttribute` attribute values, you need to specify them manually.

```php
$data = $page->toArray('*,comments');
```

This will results in:

```php
Array
(
    [author] => Array
        (
            [id] => 53dee8d26803fafe098c4769
            [name] => John Doe
        )

    [title] => First blog post
    [comments] => Array
        (
            [0] => Array
                (
                    [text] => Best blog post ever!
                    [id] => 53dee8d26803fafe098b4569
                )

        )
)
```

Besides attribute names, you can control the depth of data being returned by specifying the depth in second parameter.
Default depth is 1, which means `self + 1` (the example above is showing output of depth set to 1, by default).

Resources
---------

To run unit tests, you need to use the following command:

    $ cd path/to/Webiny/Component/Entity/
    $ composer.phar install
    $ phpunit

Make sure you set your MongoDb driver settings in `Tests\MongoExampleConfig.yaml`.