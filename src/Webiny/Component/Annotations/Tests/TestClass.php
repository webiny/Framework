<?php

namespace Webiny\Component\Annotations\Tests;

/**
 * This is a class description with some annotations.
 *
 * @prop  SomeProperty that has a string value.
 * @author.name AuthorName
 * @author.email author@name.com
 * @author.website.url http://www.webiny.com
 * @author.website.desc My website
 *
 */
class TestClass
{
    /**
     * @var SomeVarAnnotation
     * @public
     */
    var $someVar;

    /**
     * @access.role ADMIN
     * @access.level 3
     * @name SomeName
     */
    private $_anotherVar;

    /**
     * @post @get
     * @cache.ttl 10
     * @cache.store true @cache.key cacheKey
     * @accept ["json", "xml"]
     */
    public function someMethod()
    {

    }
}