<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Rest\Tests\Parser;

use Webiny\Component\Rest\Parser\PathTransformations;

class PathTransformationsTest extends \PHPUnit_Framework_TestCase
{

    public function apiUrlProvider()
    {
        return [
            [
                'Class',
                'method',
                'class/method'
            ],
            [
                'SomeClass',
                'someMethod',
                'some-class/some-method'
            ],
            [
                'SomeClassName',
                'someMethodName',
                'some-class-name/some-method-name'
            ],
            [
                'CLASS',
                'METHOD',
                'c-l-a-s-s/m-e-t-h-o-d'
            ],
            [
                'class',
                'method',
                'class/method'
            ],
            [
                'SomeClass22',
                'someMethod11',
                'some-class22/some-method11'
            ],
            [
                'Webiny\Class\SomeClass',
                'someMethod',
                'some-class/some-method'
            ]

        ];
    }

    /**
     * @dataProvider methodNameToUrlProvider
     *
     * @param $method
     * @param $expected
     */
    public function testMethodNameToUrl($method, $expected)
    {
        $this->assertSame($expected, PathTransformations::methodNameToUrl($method));
    }

    public function methodNameToUrlProvider()
    {
        return [
            [
                'someMethod',
                'some-method'
            ],
            [
                'someMethodName',
                'some-method-name'
            ],
            [
                'METHOD',
                'm-e-t-h-o-d'
            ],
            [
                'method',
                'method'
            ],
            [
                'someMethod11',
                'some-method11'
            ]

        ];
    }

    /**
     * @dataProvider classNameToUrlProvider
     *
     * @param $class
     * @param $expected
     */
    public function testClassNameToUrl($class, $expected)
    {
        $this->assertSame($expected, PathTransformations::classNameToUrl($class, true));

        $classData = explode('\\', $class);

        $className = end($classData);
        if($className=='\\'){
            $className = $class;
        }

        $this->assertSame($className, PathTransformations::classNameToUrl($class, false));
    }

    public function classNameToUrlProvider()
    {
        return [
            [
                'Class',
                'class'
            ],
            [
                'SomeClass',
                'some-class'
            ],
            [
                'SomeClassName',
                'some-class-name'
            ],
            [
                'CLASS',
                'c-l-a-s-s'
            ],
            [
                'SomeClass22',
                'some-class22'
            ],
            [
                'Webiny\Class\SomeClass',
                'some-class'
            ]
        ];
    }

}