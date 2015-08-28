Entity Component
=====================
`Entity` component is an ODM layer for MongoDB. Entity classes created using this component will serve as the main building blocks for modules, forms, tables, etc.
Every entity attribute is a complex data type. There are no simple strings or integers. Even `boolean` is a complex data type and has a separate class. This allows us to
generate a lot of code and interface elements automatically.

Install the component
---------------------
The best way to install the component is using Composer.

```bash
composer require webiny/entity
```
For additional versions of the package, visit the [Packagist page](https://packagist.org/packages/webiny/entity).

## Supported attributes
- `boolean`
- `char`
- `text`
- `integer`
- `float`
- `datetime`
- `date`
- `select`
- `arr`
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
    protected static $entityCollection = "Page";

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
$title = $page->title;
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

### setOnce()
This method allows you to protect an attribute from being updated. You use this method to only allow your attribute to be populated when your new entity instance has no ID set (meaning it's a new instance).
After you save your new entity instance, all subsequent calls to `populate()` will skip this attribute.

## One2Many Attribute
This attribute's value is an instance of `EntityCollection`. You can you is in `foreach` loops, access values by numeric indexes and also call `count()` method to find out the total number of items in the data set.

```php
$page = Page::findById("53712ed46803fa4e058b456b");
echo $page->comments->count();

foreach($page->comments as $comment){
...
}
```

### Mass populating One2ManyAttribute
There are different ways to populate One2Many attributes from, say, POST request.

1) Structure data as simple array of Entity IDs:
```php
$data = [
    'title'    => 'My shiny title',
    'comments' => [
        '543c0fb76803fa76058b4569',
        '543c0fda6803fa76058b456f'
    ]
];
```

2) Structure data as array of arrays with Entity data. If array contains `id`, an existing instance will be loaded, and populated with any data specified in the array (useful for updating existing Entities):
```php
$data = [
    'title'    => 'My shiny title',
    'comments' => [
        ['id' => '543c0fb76803fa76058b4569', 'someAttribute' => 'newValue'],
        ['id' => '543c0fda6803fa76058b456f']
    ]
];
```

3) Structure data as `array` or `EntityCollection` of `EntityAbstract` instances. Using `find` method:

```php
$entityCollection = Comment::find(['status' => 'approved']);

$data = [
    'title'    => 'My shiny title',
    'comments' => $entityCollection
];
```

Or if you build your array manually...
```php
$instance1 = Comment::findById($id1);
$instance2 = Comment::findById($id2);
$array = [$instance1, $instance2];

$data = [
    'title'    => 'My shiny title',
    'comments' => $array
];
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
## Linking with other entities
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

## ArrayAttribute
This attribute is mostly used for some extra data you want to store with your entity that is not shown to users in forms or grids (some settings, etc.).
The cool thing about this attribute is it's "set" and "get" methods, which allows you to get and set nested keys and get default value if some part of your key does not exist.

```php
// $page->settings is an instance of ArrayAttribute

// Set value by specifying whatever nesting level you want
// Even if some of the levels may not exist along the way - they will be created for you automatically
$page->settings->set('level1.level2.level3', 'My new value');

// Get value from key that may not exist
$page->settings->get('level1.level4', 'Default value');

// You can also append values like this
$page->settings[] = 'New value';

// Or using an 'append' method
$page->settings->append('New value');

// And you can also prepend values
$page->settings->prepend('New value');
```

## Default value for Many2OneAttribute
Default value for `Many2OneAttribute` can be specified in several ways:
```php
// Provide a default entity ID (can be fetched from logged in user, or retrieved from database or whatever)
$defaultId = '53712ed46803fa4e058b456b';
$this->attr('author')->many2one()->setEntity('Author')->setDefaultValue($defaultId);
```

```php
// Provide a default entity instance
$defaultInstance = Author::find(['some' => 'condition']);
// or
$defaultInstance = Author::findById('53712ed46803fa4e058b456b');
$this->attr('author')->many2one()->setEntity('Author')->setDefaultValue($defaultInstance);
```

```php
// Provide data which will be used to populate new entity instance
// (will create a new record with new ID each time a default value is used)
$defaultData = [
    'firstName' => 'Pavel', 
    'lastName' => 'Denisjuk'
];
$this->attr('author')->many2one()->setEntity('Author')->setDefaultValue($defaultData);
```

## Finding entities
There are 3 methods that allow you to find your entities: `find`, `findById` and `findOne`.

### find(array $conditions = [], array $order = [], $limit = 0, $page = 0) `EntityCollection`
- $conditions - is a key => value array of attributes and their values
- $order - is an array of sorters defined as `['-name', '+title', 'lastName']` 
  ('-' equals to DESCENDING, '+' or no prefix equals to ASCENDING)
- $limit - number of entities to return
- $page - this will be used to calculate offset for you. NOTE: $page values start with 1. Ex: $limit=10, $page=2 will skip the first 10 records and return the next 10.

This method returns an instance of [EntityCollection](#entitycollection-class).

```php
// Load Pages
$pages = Page::find(['active' => true], ['-title'], 5, 2);
$count = $pages->count();
foreach($pages as $page){
...
}
```

### findById($id) `EntityAbstract`
Returns an instance of `EntityAbstract` by given $id. If no entity is found, `null` is returned.

```php
// Load Page
$page = Page::findById("53712ed46803fa4e058b456b");
```

### findOne(array $conditions = []) `EntityAbstract`
Returns an instance of `EntityAbstract` by given $conditions. If no entity is found, `null` is returned.

```php
// Load Page
$page = Page::findOne(['title' => 'First blog post']);
```

## EntityCollection class
This class is used to return results of find() method. It implements `IteratorAggregate` and `ArrayAccess` interfaces so it behaves exactly as an ordinary array would, and it also contains some utility methods to help you work with the data:

- `toArray($fields = '')` - returns an array representation of all entities in the resultset ([see this for more details](#convert-entityabstract-to-array))
- `add($item)` - adds $item to resultset (used with One2Many and Many2Many attributes to add new items to the attribute value)
- `count()` - returns number of items in the resultset
- `totalCount()` - returns total number of items without $limit and $page parameters
- `contains($item)` - checks if given $item already exists in the resultset
- `delete()` - deletes all items in the resultset (removes them from database)
- `removeItem($item)` - removes item from the resultset (without removing them from database. This method is used with Many2Many attributes, to remove links between entities)

## Convert EntityAbstract to array
You can get an array representation of current `EntityAbstract` instance by calling `toArray()` method.
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
    [author] => Arrayf
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