<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\StdLib\Tests\StdObject;

use Webiny\Component\StdLib\StdObject\DateTimeObject\DateTimeObject;

class DateTimeObjectTest extends \PHPUnit_Framework_TestCase
{

    function testConstruct()
    {
        $dt = new DateTimeObject();

        $this->assertSame(time(), $dt->getTimestamp());
    }

    function testConstructor2()
    {
        $dt = new DateTimeObject('14.02.2013');
        $dt->setFormat('d.m.Y');

        $this->assertSame('14.02.2013', $dt->val());
    }

    public function testConstructor3()
    {
        $timestamp = time();
        $dt = new DateTimeObject($timestamp);

        $this->assertSame($timestamp, $dt->getTimestamp());
    }

    function testCreateFromFormat()
    {
        $dt = DateTimeObject::createFromFormat('15-Feb-2009', 'j-M-Y');

        $this->assertSame('15-Feb-2009', $dt->val());
    }

    function testDiff()
    {
        $start = new DateTimeObject('14.02.2013');
        $end = new DateTimeObject('03.03.2013');

        $diff = $start->diff($end);

        $this->assertSame(17, $diff->key('d'));
    }

    function testGetOffset()
    {
        $dt = new DateTimeObject("now", 'Europe/Zagreb');

        $this->assertSame(7200, $dt->getOffset());
    }

    function testGetTimezone()
    {
        $dt = new DateTimeObject("now", 'Europe/Zagreb');

        $this->assertSame('Europe/Zagreb', $dt->getTimezone());
    }

    function testGetDate()
    {
        $dt = new DateTimeObject('14.02.2013');

        $this->assertSame("2013-02-14T00:00:00+01:00", $dt->getDate());
    }

    function testGetYear()
    {
        $dt = new DateTimeObject('14.02.2013');

        $this->assertSame("13", $dt->getYear('y'));
    }

    /**
     * @expectedException \Webiny\Component\StdLib\StdObject\StdObjectException
     * @expectedExceptionMessage Invalid format m for getYear
     */
    function testGetYear2()
    {
        $dt = new DateTimeObject('14.02.2013');

        $this->assertSame("13", $dt->getYear('m'));
    }

    function testGetMonth()
    {
        $dt = new DateTimeObject('14.02.2013');

        $this->assertSame("02", $dt->getMonth());
    }

    function testGetWeek()
    {
        $dt = new DateTimeObject('14.02.2013');

        $this->assertSame("07", $dt->getWeek());
    }

    function testGetDay()
    {
        $dt = new DateTimeObject('14.02.2013');

        $this->assertSame("14", $dt->getDay());
    }

    function testGetTime()
    {
        $dt = new DateTimeObject('14.02.2013 14:33:18');

        $this->assertSame("14:33:18", $dt->getTime());
    }

    function testGetHours()
    {
        $dt = new DateTimeObject('14.02.2013 14:33:18');

        $this->assertSame("14", $dt->getHours());
    }

    function testGetMinutes()
    {
        $dt = new DateTimeObject('14.02.2013 14:33:18');

        $this->assertSame("33", $dt->getMinutes());
    }

    function testGetSeconds()
    {
        $dt = new DateTimeObject('14.02.2013 14:33:18');

        $this->assertSame("18", $dt->getSeconds());
    }

    function testGetMeridiem()
    {
        $dt = new DateTimeObject('14.02.2013 14:33:18');

        $this->assertSame("pm", $dt->getMeridiem());
    }

    function testAdd()
    {
        $dt = new DateTimeObject('14.02.2013');
        $dt->add("5 days");

        $this->assertSame('19.02.2013', $dt->format('d.m.Y'));
    }

    function testAdd2()
    {
        $dt = new DateTimeObject('14.02.2013');
        $dt->add("P5D");

        $this->assertSame('19.02.2013', $dt->format('d.m.Y'));
    }

    function testSetDate()
    {
        $dt = new DateTimeObject('14.02.2013');
        $dt->setDate('2014', '01', '15');

        $this->assertSame('15.01.2014', $dt->format('d.m.Y'));
    }

    function testSetTime()
    {
        $dt = new DateTimeObject('14.02.2013 12:00:00');
        $dt->setDate('2014', '01', '15')->setTime('14', '25');

        $this->assertSame('14:25:00', $dt->format('H:i:s'));
    }

    function testSub()
    {
        $dt = new DateTimeObject('14.02.2013');
        $dt->sub("5 days");

        $this->assertSame('09.02.2013', $dt->format('d.m.Y'));
    }

    function testOffsetToTimezone()
    {
        $dt = new DateTimeObject('14.02.2013 12:00:00');
        $dt->offsetToTimezone('Europe/London');

        $this->assertSame('11:00:00', $dt->format('H:i:s'));
    }

    function testIsLeap()
    {
        $dt = new DateTimeObject('14.02.2013');

        $this->assertFalse($dt->isLeap());
    }

    function testIsLeap2()
    {
        $dt = new DateTimeObject('01.01.2012');

        $this->assertTrue($dt->isLeap());
    }

    function testIsFuture()
    {
        $dt = new DateTimeObject('14.02.2013');

        $this->assertFalse($dt->isFuture());
    }

    function testIsFuture2()
    {
        $dt = new DateTimeObject('01.01.2020');

        $this->assertTrue($dt->isFuture());
    }

    function testIsPast()
    {
        $dt = new DateTimeObject('14.02.2020');

        $this->assertFalse($dt->isPast());
    }

    function testIsPast2()
    {
        $dt = new DateTimeObject('01.01.2002');

        $this->assertTrue($dt->isPast());
    }

    function testLargerThan()
    {
        $dt = new DateTimeObject('14.02.2013');

        $this->assertTrue($dt->largerThan('13.02.2013'));
    }

    function testLargerThan2()
    {
        $dt = new DateTimeObject('14.02.2013');

        $this->assertFalse($dt->largerThan('15.02.2013'));
    }

    function testSmallerThan()
    {
        $dt = new DateTimeObject('14.02.2013');

        $this->assertTrue($dt->smallerThan('15.02.2013'));
    }

    function testSmallerThan2()
    {
        $dt = new DateTimeObject('14.02.2013');

        $this->assertFalse($dt->smallerThan('13.02.2013'));
    }
}