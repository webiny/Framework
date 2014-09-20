<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\StdLib\Tests\StdObject;

use Webiny\Component\StdLib\StdObject\ArrayObject\ArrayObject;

class ArrayObjectTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @expectedException \Webiny\Component\StdLib\StdObject\StdObjectException
     */
    public function testConstructorException()
    {
        $a = new ArrayObject('value');
    }

    /**
     * @expectedException \Webiny\Component\StdLib\StdObject\StdObjectException
     */
    public function testConstructorException2()
    {
        $a = new ArrayObject('key', 'value');
    }

    public function testConstructorKeyOnly()
    {
        $a = new ArrayObject(['key']);
        $this->assertSame(array('key'), $a->val());
    }

    public function testConstructorKeyValue()
    {
        $a = new ArrayObject(['key' => 'value']);
        $this->assertSame(array('key' => 'value'), $a->val());
    }

    public function testConstructorCombine()
    {
        $a = new ArrayObject([
                                 'key1',
                                 'key2'
                             ], [
                                 'value1',
                                 'value2'
                             ]
        );
        $this->assertSame(array(
                              'key1' => 'value1',
                              'key2' => 'value2'
                          ), $a->val()
        );
    }

    /**
     * @expectedException \Webiny\Component\StdLib\StdObject\StdObjectException
     */
    public function testConstructorCombine2()
    {
        $a = new ArrayObject([
                                 'key1',
                                 'key2',
                                 ''
                             ], [
                                 'value1',
                                 'value2',
                                 'value3',
                                 'value4',
                                 'value5'
                             ]
        );
        $this->assertSame(array(
                              'key1' => 'value1',
                              'key2' => 'value2'
                          ), $a->val()
        );
    }

    public function testSum()
    {
        $a = new ArrayObject([
                                 1,
                                 2,
                                 3
                             ]
        );
        $sum = $a->sum();
        $this->assertSame(6, $sum);
    }

    /**
     * @dataProvider arraySet1
     */
    public function testKeys($array)
    {
        $a = new ArrayObject($array);
        $keys = $a->keys();

        $this->assertSame(array_keys($array), $keys->val());
    }

    /**
     * @dataProvider arraySet1
     */
    public function testValues($array)
    {
        $a = new ArrayObject($array);
        $values = $a->values();

        $this->assertSame(array_values($array), $values->val());
    }

    /**
     * @dataProvider arraySet1
     */
    public function testLast($array)
    {
        $a = new ArrayObject($array);
        $last = $a->last();

        $this->assertSame(end($array), $last->val());
    }

    /**
     * @dataProvider arraySet1
     */
    public function testFirst($array)
    {
        $a = new ArrayObject($array);
        $last = $a->first();

        $firstValue = reset($array);
        $this->assertSame($firstValue, $last->val());
    }

    /**
     * @dataProvider arraySet1
     */
    public function testCount($array)
    {
        $a = new ArrayObject($array);
        $count = $a->count();

        $this->assertSame(count($array), $count);
    }

    /**
     * @dataProvider arraySet2
     */
    public function testCountValues($array)
    {
        $a = new ArrayObject($array);
        $valueCount = $a->countValues();

        $this->assertSame(array_count_values($array), $valueCount->val());
    }

    /**
     * @dataProvider arraySet1
     */
    public function testGetValue($array)
    {
        $a = new ArrayObject($array);

        $this->assertSame($array, $a->val());
    }

    /**
     * @dataProvider arraySet1
     */
    public function testUpdateValue($array)
    {
        $a = new ArrayObject($array);
        $a->val([
                    'k1',
                    'k2' => 'v2'
                ]
        );

        $this->assertSame([
                              'k1',
                              'k2' => 'v2'
                          ], $a->val()
        );
    }

    /**
     * @dataProvider arraySet1
     */
    public function testAppend($array)
    {
        $a = new ArrayObject($array);
        $a->append('k512');

        array_push($array, 'k512');
        $this->assertSame($array, $a->val());
    }

    /**
     * @dataProvider arraySet1
     */
    public function testPrepend($array)
    {
        $a = new ArrayObject($array);
        $a->prepend('k512');

        array_unshift($array, 'k512');
        $this->assertSame($array, $a->val());
    }

    /**
     * @dataProvider arraySet2
     */
    public function testPrepend2($array)
    {
        $a = new ArrayObject($array);
        $a->prepend('k512', 'val');

        $array = array('k512' => 'val') + $array;
        $this->assertSame($array, $a->val());
    }

    /**
     * @dataProvider arraySet1
     */
    public function testRemoveFirst($array)
    {
        $a = new ArrayObject($array);
        $a->removeFirst();

        array_shift($array);
        $this->assertSame($array, $a->val());
    }

    /**
     * @dataProvider arraySet1
     */
    public function testRemoveLast($array)
    {
        $a = new ArrayObject($array);
        $a->removeLast();

        array_pop($array);
        $this->assertSame($array, $a->val());
    }

    public function testRemoveKey()
    {
        $array = [
            'k1' => 'val',
            'k2' => null,
            'k3' => false
        ];
        $a = new ArrayObject($array);
        $a->removeKey('k2');

        unset($array['k2']);
        $this->assertSame($array, $a->val());
    }

    /**
     * @dataProvider arraySet1
     */
    public function testImplode($array)
    {
        $a = new ArrayObject($array);
        @$string = $a->implode(' ');

        @$string2 = implode(' ', $array);
        $this->assertSame($string2, $string->val());
    }

    /**
     * @dataProvider arraySet1
     */
    public function testChunk($array)
    {
        $a = new ArrayObject($array);
        $chunk = $a->chunk(2, true);

        $chunk2 = array_chunk($array, 2, true);
        $this->assertSame($chunk2, $chunk->val());
    }

    /**
     * @dataProvider arraySet1
     */
    public function testChangeKeyCase($array)
    {
        $a = new ArrayObject($array);
        $a->changeKeyCase('upper');

        $array = array_change_key_case($array, CASE_UPPER);
        $this->assertSame($array, $a->val());
    }

    /**
     * @expectedException \Webiny\Component\StdLib\StdObject\StdObjectException
     */
    public function testChangeKeyCase2()
    {
        $a = new ArrayObject(['k1']);
        $a->changeKeyCase('mid-case');
    }

    /**
     * @dataProvider arraySet1
     */
    public function testFillKeys($array)
    {
        $a = new ArrayObject($array);
        @$a->fillKeys('value');

        @$array = array_fill_keys($array, 'value');
        $this->assertSame($array, $a->val());
    }

    public function testFill()
    {
        $array = [
            'test1',
            'test2'
        ];
        $a = new ArrayObject($array);
        $a->fill(2, 2, 'value');

        @$array = array_fill(2, 2, 'value');
        $this->assertSame($array, $a->val());
    }

    /**
     * @expectedException \Webiny\Component\StdLib\StdObject\StdObjectException
     */
    public function testFilter()
    {
        $a = new ArrayObject([]);
        $a->filter('notCallable');
    }

    /**
     * @dataProvider arraySet1
     */
    public function testFilter2($array)
    {
        // callback function used for filtering
        $callable = function ($item) {
            if (is_string($item) && strpos($item, 'v') !== false) {
                return $item;
            }

            return false;
        };

        $a = new ArrayObject($array);
        $a->filter($callable);

        $array = array_filter($array, $callable);
        $this->assertSame($array, $a->val());
    }

    /**
     * @dataProvider arraySet1
     */
    public function testFlip($array)
    {
        $a = new ArrayObject($array);
        @$a->flip();

        @$array = array_flip($array);
        $this->assertSame($array, $a->val());
    }

    /**
     * @dataProvider arraySet1
     */
    public function testIntersect($array)
    {
        $compare = [
            'v1',
            'v2',
            'v3',
            'vv1',
            'vvv2',
            'vvv3'
        ];
        $a = new ArrayObject($array);
        @$a->intersect($compare);

        @$array = array_intersect($array, $compare);
        $this->assertSame($array, $a->val());
    }

    /**
     * @dataProvider arraySet1
     */
    public function testIntersectAssoc($array)
    {
        $compare = [
            'k1' => 'v1',
            'k2' => 'v2',
            'k3' => 'v3',
            'vv1',
            'vvv2',
            'vvv3'
        ];
        $a = new ArrayObject($array);
        @$a->intersectAssoc($compare);

        @$array = array_intersect_assoc($array, $compare);
        $this->assertSame($array, $a->val());
    }

    /**
     * @dataProvider arraySet1
     */
    public function testIntersectAssoc2($array)
    {
        $compare = ['k1' => 'v1'];

        // callback function used for filtering
        $callable = function ($a, $b) {
            if ($a == $b) {
                return 0;
            }

            return 1;
        };

        $a = new ArrayObject($array);
        @$a->intersectAssoc($compare, $callable);

        @$array = array_intersect_uassoc($array, $compare, $callable);
        $this->assertSame($array, $a->val());
    }

    /**
     * @dataProvider arraySet1
     */
    public function testIntersectKey($array)
    {
        $compare = [
            'k1' => 'v1',
            'k2' => 'v2',
            'k3' => 'v3',
            'vv1',
            'vvv2',
            'vvv3'
        ];
        $a = new ArrayObject($array);
        @$a->intersectKey($compare);

        @$array = array_intersect_key($array, $compare);
        $this->assertSame($array, $a->val());
    }

    /**
     * @dataProvider arraySet1
     */
    public function testIntersectKey2($array)
    {
        $compare = ['k1' => 'v1'];

        // callback function used for filtering
        $callable = function ($a, $b) {
            if ($a == $b) {
                return 0;
            }

            return 1;
        };

        $a = new ArrayObject($array);
        @$a->intersectKey($compare, $callable);

        @$array = array_intersect_ukey($array, $compare, $callable);
        $this->assertSame($array, $a->val());
    }

    /**
     * @dataProvider arraySet1
     */
    public function testMap($array)
    {
        // callback function used for mapping
        $callable = function ($item) {
            if (is_array($item)) {
                $item['new'] = 'value';
            } else {
                $item = $item . '_appended';
            }

            return $item;
        };

        $a = new ArrayObject($array);
        $a->map($callable);

        $array = array_map($callable, $array);
        $this->assertSame($array, $a->val());
    }

    /**
     * @dataProvider arraySet1
     */
    public function testMerge($array)
    {
        $a = new ArrayObject($array);
        $a->merge($array);

        $array = array_merge($array, $array);
        $this->assertSame($array, $a->val());
    }

    /**
     * @dataProvider arraySet1
     */
    public function testSortAssoc($array)
    {
        $a = new ArrayObject($array);
        $a->sortAssoc();

        asort($array);
        $this->assertSame($array, $a->val());
    }

    /**
     * @dataProvider arraySet1
     */
    public function testSortAssoc2($array)
    {
        $a = new ArrayObject($array);
        @$a->sortAssoc(SORT_DESC);

        arsort($array);
        $this->assertSame($array, $a->val());
    }


    /**
     * @dataProvider arraySet1
     */
    public function testSortKey($array)
    {
        $a = new ArrayObject($array);
        $a->sortKey();

        ksort($array);
        $this->assertSame($array, $a->val());
    }

    /**
     * @dataProvider arraySet1
     */
    public function testSortKey2($array)
    {
        $a = new ArrayObject($array);
        @$a->sortKey(SORT_DESC);

        krsort($array);
        $this->assertSame($array, $a->val());
    }

    public function testSortField()
    {
        $array = [
            1 => ['order' => 3],
            2 => ['order' => 2],
            3 => ['order' => 1]
        ];

        $a = new ArrayObject($array);
        $a->sortField('order');

        $sortedArray = [
            3 => ['order' => 1],
            2 => ['order' => 2],
            1 => ['order' => 3],
        ];

        $this->assertSame($sortedArray, $a->val());
    }

    public function testSortField2()
    {
        $array = [
            1 => ['order' => 3],
            2 => ['order' => 2],
            3 => ['order' => 1]
        ];

        $a = new ArrayObject($array);
        $a->sortField('order', SORT_DESC);

        $this->assertSame($array, $a->val());
    }

    /**
     * @dataProvider arraySet1
     */
    public function testPad($array)
    {
        $a = new ArrayObject($array);
        $a->pad(10, 'testValue');

        $array = array_pad($array, 10, 'testValue');
        $this->assertSame($array, $a->val());
    }

    /**
     * @dataProvider arraySet1
     */
    public function testReplace($array)
    {
        $replacements = [
            'k1' => 'test',
            'k2' => 'test'
        ];

        $a = new ArrayObject($array);
        $a->replace($replacements);

        $array = array_replace($array, $replacements);
        $this->assertSame($array, $a->val());
    }

    /**
     * @dataProvider arraySet1
     */
    public function testReverse($array)
    {

        $a = new ArrayObject($array);
        $a->reverse();

        $array = array_reverse($array);
        $this->assertSame($array, $a->val());
    }

    /**
     * @dataProvider arraySet1
     */
    public function testSlice($array)
    {
        $a = new ArrayObject($array);
        $a->slice(0, 1);

        $array = array_slice($array, 0, 1, true);
        $this->assertSame($array, $a->val());
    }

    /**
     * @dataProvider arraySet1
     */
    public function testSplice($array)
    {
        $a = new ArrayObject($array);
        @$a->splice(0, -1, 'replacement');

        $array = array_splice($array, 0, -1, 'replacement');
        $this->assertSame($array, $a->val());
    }

    public function testUnique()
    {
        $array = [
            'v1',
            'v2',
            'v1'
        ];
        $a = new ArrayObject($array);
        $a->unique();

        $array = array_unique($array);
        $this->assertSame($array, $a->val());
    }

    /**
     * @dataProvider arraySet1
     */
    public function testWalk($array)
    {
        // callback function used for mapping
        $callable = function ($item) {
            if (is_array($item)) {
                $item['new'] = 'value';
            } else {
                $item = $item . '_appended';
            }

            return $item;
        };

        $a = new ArrayObject($array);
        $a->walk($callable);

        array_walk($array, $callable);
        $this->assertSame($array, $a->val());
    }

    /**
     * @dataProvider arraySet1
     */
    public function testDiff($array)
    {
        $compareArray = [
            'v1',
            'v2'
        ];

        $a = new ArrayObject($array);
        @$a->diff($compareArray);

        @$array = array_diff($array, $compareArray);
        $this->assertSame($array, $a->val());
    }

    /**
     * @dataProvider arraySet1
     */
    public function testDiff2($array)
    {
        $compareArray = [
            'v1',
            'v2'
        ];

        $a = new ArrayObject($array);
        @$a->diff($compareArray, true);

        @$array = array_diff_assoc($array, $compareArray);
        $this->assertSame($array, $a->val());
    }

    /**
     * @dataProvider arraySet1
     */
    public function testDiffKeys($array)
    {
        $compareArray = [
            'k1',
            'k2'
        ];

        $a = new ArrayObject($array);
        @$a->diffKeys($compareArray);

        @$array = array_diff_key($array, $compareArray);
        $this->assertSame($array, $a->val());
    }

    /**
     * @dataProvider arraySet1
     */
    public function testSearch($array)
    {
        $a = new ArrayObject($array);
        $searchResult = $a->inArray('youCantFindMe');

        $this->assertSame(false, $searchResult);
    }

    /**
     * @dataProvider arraySet1
     */
    public function testSearch2($array)
    {
        $a = new ArrayObject($array);
        $searchResult = $a->inArray('v1');

        $key = in_array('v1', $array);
        $this->assertSame($key, $searchResult);
    }

    /**
     * @dataProvider arraySet1
     */
    public function testKey($array)
    {
        $a = new ArrayObject($array);
        $searchResult = $a->keyExists('NonExistingKey');

        $this->assertSame(false, $searchResult);
    }

    public function testGetNestedKeys()
    {
        $array = [
            'k1' => 'test',
            'k2' => [
                'k3' => [
                    'k4' => 'deepest'
                ]
            ]
        ];

        $a = new ArrayObject($array);

        $this->assertEquals('deepest', $a->keyNested('k2.k3.k4'));
        $this->assertEquals('default', $a->keyNested('k2.k7', 'default', true));
    }

    public function testSetNestedKeys()
    {
        $array = [
            'k1' => 'test',
            'k2' => [
                'k3' => [
                    'k4' => 'deepest'
                ]
            ]
        ];

        $a = new ArrayObject($array);
        $a->key('k2.k3.k4', 'webiny');
        $a->key('k2.k3.k5', 'anotherElement');

        $this->assertEquals('webiny', $a->key('k2.k3.k4'));
        $this->assertEquals('anotherElement', $a->key('k2.k3.k5'));
    }

    public function arraySet1()
    {
        return array(
            [[]],
            [['k1']],
            [['k1' => '']],
            [['' => 'v1']],
            [['k1' => false]],
            [
                [
                    'k1' => null,
                    false
                ]
            ],
            [['' => null]],
            [
                [
                    '' => false,
                    null
                ]
            ],
            [
                [
                    'k1',
                    'k2',
                    'k3'
                ]
            ],
            [
                [
                    'k1' => 'v1',
                    'k2' => 'v2',
                    'k3' => 'v3'
                ]
            ],
            [
                [
                    'k1'  => [
                        'kk1' => 'v1',
                        'kk2' => 'v2'
                    ],
                    'k2'  => 'v2',
                    'kk3' => ['vv3' => ['kk33' => 'vvv3']]
                ]
            ]
        );
    }

    public function arraySet2()
    {
        return array(
            [[]],
            [['k1']],
            [['k1' => '']],
            [['' => 'v1']],
            [
                [
                    'k1',
                    'k2',
                    'k3'
                ]
            ],
            [
                [
                    'k1' => 'v1',
                    'k2' => 'v2',
                    'k3' => 'v3'
                ]
            ]
        );
    }

}